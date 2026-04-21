<?php

declare(strict_types=1);

/*
 * This file is part of the "repeatable_form_elements" TYPO3 CMS extension.
 *
 * (c) 2018-2026 Konrad Michalik <km@move-elevator.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TRITUM\RepeatableFormElements\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use TRITUM\RepeatableFormElements\Event\{AfterBuildingFinishedEvent, CopyVariantEvent};
use TRITUM\RepeatableFormElements\FormElements\RepeatableContainerInterface;
use TypeError;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\{ArrayUtility, GeneralUtility};
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\{CompositeRenderableInterface, RenderableInterface, RenderableVariant};
use TYPO3\CMS\Form\Domain\Runtime\{FormRuntime, FormState};
use TYPO3\CMS\Form\Mvc\ProcessingRule;
use TYPO3\CMS\Form\Service\TranslationService;

use function array_key_exists;
use function assert;
use function count;
use function in_array;
use function is_array;

/**
 * CopyService.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
class CopyService // @phpstan-ignore complexity.classLike
{
    protected FormRuntime $formRuntime;
    protected FormState $formState;
    protected FormDefinition $formDefinition;
    /** @var array<string, RepeatableContainerInterface|null> */
    protected array $repeatableContainersByOriginalIdentifier = [];
    /** @var array<string, array<string, mixed>> */
    protected array $typeDefinitions = [];
    protected Features $features;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(FormRuntime $formRuntime)
    {
        $this->formRuntime = $formRuntime;
        $formState = $formRuntime->getFormState();
        assert($formState instanceof FormState, 'FormState must be available when CopyService is used');
        $this->formState = $formState;
        $this->formDefinition = $formRuntime->getFormDefinition();
        $this->typeDefinitions = $this->formDefinition->getTypeDefinitions();
        $this->features = GeneralUtility::makeInstance(Features::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @api
     */
    public function createCopiesFromCurrentRequest(): void
    {
        $requestArguments = $this->formRuntime->getRequest()->getArguments();
        $this->removeDeletedRepeatableContainersFromFormValuesByRequest($requestArguments);
        $requestArguments = array_replace_recursive(
            $this->formState->getFormValues(),
            $requestArguments,
        );

        $this->copyRepeatableContainersFromArguments($requestArguments);
    }

    /**
     * @api
     */
    public function createCopiesFromFormState(): void
    {
        $this->copyRepeatableContainersFromArguments($this->formState->getFormValues());
    }

    /**
     * @return ProcessingRule[]
     *
     * @internal
     */
    public function copyProcessingRule(
        string $originalFormElement,
        string $newElementCopy,
    ): array {
        $originalProcessingRule = $this->formRuntime->getFormDefinition()->getProcessingRule($originalFormElement);

        GeneralUtility::addInstance(PropertyMappingConfiguration::class, $originalProcessingRule->getPropertyMappingConfiguration());
        $newProcessingRule = $this->formRuntime->getFormDefinition()->getProcessingRule($newElementCopy);

        try {
            $newProcessingRule->setDataType($originalProcessingRule->getDataType());
        } catch (TypeError) {
        }

        return [$originalProcessingRule, $newProcessingRule];
    }

    /**
     * Dispatch the afterBuildingFinished event/hook for a renderable.
     * In v13: dispatches both the legacy SC_OPTIONS hook and the PSR-14 event.
     * In v14+: the SC_OPTIONS hook no longer exists, only the PSR-14 event fires.
     */
    public function dispatchAfterBuildingFinished(RenderableInterface $renderable): void
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'] ?? [] as $className) {
            $hookObj = GeneralUtility::makeInstance($className); // @phpstan-ignore argument.templateType
            if (method_exists($hookObj, 'afterBuildingFinished')) {
                $hookObj->afterBuildingFinished($renderable);
            }
        }

        $this->eventDispatcher->dispatch(new AfterBuildingFinishedEvent($renderable));
    }

    /**
     * @param array<string, mixed>   $requestArguments
     * @param array<int, string|int> $argumentPath
     */
    protected function copyRepeatableContainersFromArguments(
        array $requestArguments,
        array $argumentPath = [],
    ): void {
        foreach ($requestArguments as $argumentKey => $argumentValue) {
            if (!is_array($argumentValue)) {
                continue;
            }

            $originalContainer = $this->getRepeatableContainerByOriginalIdentifier($argumentKey);
            $copyIndexes = array_keys($argumentValue);
            unset($copyIndexes[0]);
            $argumentPath[] = $argumentKey;

            if ($originalContainer instanceof RepeatableContainerInterface
                && [] === array_filter(array_keys($copyIndexes), is_string(...)) // @phpstan-ignore identical.alwaysTrue
            ) {
                $this->processCopyIndexes($originalContainer, $copyIndexes, $argumentPath);
            }

            $this->copyRepeatableContainersFromArguments($argumentValue, $argumentPath);
            array_pop($argumentPath);
        }
    }

    protected function copyRepeatableContainer(
        RepeatableContainerInterface $copyFromContainer,
        RepeatableContainerInterface $moveAfterContainer,
        string $newIdentifier,
    ): RepeatableContainerInterface {
        $typeName = $copyFromContainer->getType();
        $implementationClassName = $this->typeDefinitions[$typeName]['implementationClassName'];
        $parentRenderable = $moveAfterContainer->getParentRenderable();
        assert($parentRenderable instanceof CompositeRenderableInterface);

        /** @var RepeatableContainerInterface $newContainer */
        $newContainer = GeneralUtility::makeInstance($implementationClassName, $newIdentifier, $typeName); // @phpstan-ignore argument.templateType
        $this->copyOptions($newContainer, $copyFromContainer); // @phpstan-ignore argument.type, argument.type

        $parentRenderable->addElement($newContainer); // @phpstan-ignore method.notFound
        $parentRenderable->moveElementAfter($newContainer, $moveAfterContainer); // @phpstan-ignore method.notFound

        $this->dispatchAfterBuildingFinished($newContainer);

        foreach ($copyFromContainer->getElements() as $originalFormElement) {
            $this->createNestedElements($originalFormElement, $newContainer, $copyFromContainer->getIdentifier(), $newIdentifier);
        }

        return $newContainer;
    }

    protected function copyOptions(
        FormElementInterface $newElementCopy,
        FormElementInterface $originalFormElement,
    ): void {
        $newElementCopy->setLabel($originalFormElement->getLabel()); // @phpstan-ignore method.notFound
        $newElementCopy->setDefaultValue($originalFormElement->getDefaultValue());
        foreach ($originalFormElement->getProperties() as $key => $value) {
            $newElementCopy->setProperty($key, $value);
        }
        foreach ($originalFormElement->getRenderingOptions() as $key => $value) {
            if (
                '_isRootRepeatableContainer' === $key
                || '_originalIdentifier' === $key
                || '_isReferenceContainer' === $key
            ) {
                continue;
            }
            $newElementCopy->setRenderingOption($key, $value);
        }

        [$originalProcessingRule] = $this->copyProcessingRule($originalFormElement->getIdentifier(), $newElementCopy->getIdentifier());

        /** @var ValidatorInterface $validator */
        foreach ($originalProcessingRule->getValidators() as $validator) {
            $newElementCopy->addValidator($validator);
        }
    }

    protected function createNestedElements(
        FormElementInterface $originalFormElement,
        CompositeRenderableInterface $parentFormElementCopy,
        string $identifierOriginal,
        string $identifierReplacement,
    ): void {
        $newIdentifier = str_replace($identifierOriginal, $identifierReplacement, $originalFormElement->getIdentifier());
        $newFormElement = $parentFormElementCopy->createElement( // @phpstan-ignore method.notFound
            $newIdentifier,
            $originalFormElement->getType(),
        );
        $this->copyOptions($newFormElement, $originalFormElement);
        $this->copyProcessingRule($originalFormElement->getIdentifier(), $newIdentifier);
        $this->copyVariants($originalFormElement, $newFormElement, $newIdentifier);

        $this->dispatchAfterBuildingFinished($newFormElement);

        if ($originalFormElement instanceof CompositeRenderableInterface) {
            foreach ($originalFormElement->getElements() as $originalChildFormElement) { // @phpstan-ignore method.notFound
                $this->createNestedElements($originalChildFormElement, $newFormElement, $identifierOriginal, $identifierReplacement);
            }
        }
    }

    protected function getRepeatableContainerByOriginalIdentifier(string $originalIdentifier): ?RepeatableContainerInterface
    {
        if (array_key_exists($originalIdentifier, $this->repeatableContainersByOriginalIdentifier)) {
            return $this->repeatableContainersByOriginalIdentifier[$originalIdentifier];
        }

        foreach ($this->formDefinition->getRenderablesRecursively() as $formElement) {
            $renderingOptions = $formElement->getRenderingOptions();
            if (
                $formElement instanceof RepeatableContainerInterface
                && ($renderingOptions['_originalIdentifier'] ?? null) === $originalIdentifier
                && (bool) ($renderingOptions['_isRootRepeatableContainer'] ?? false)
            ) {
                $this->repeatableContainersByOriginalIdentifier[$originalIdentifier] = $formElement;

                return $formElement;
            }
        }

        $this->repeatableContainersByOriginalIdentifier[$originalIdentifier] = null;

        return null;
    }

    protected function addError(
        FormElementInterface|RepeatableContainerInterface $formElement,
        int $timestamp,
        string $defaultMessage = '',
    ): void {
        $error = GeneralUtility::makeInstance(
            Error::class,
            GeneralUtility::makeInstance(TranslationService::class)->translateFormElementError(
                $formElement,
                $timestamp,
                [],
                $defaultMessage,
                $this->formRuntime,
            ),
            $timestamp,
        );
        $this->formDefinition
            ->getProcessingRule($formElement->getIdentifier())
            ->getProcessingMessages()
            ->addError($error);
    }

    /**
     * @param array<string, mixed>   $requestArguments
     * @param array<int, string|int> $argumentPath
     */
    protected function removeDeletedRepeatableContainersFromFormValuesByRequest(
        array $requestArguments,
        array $argumentPath = [],
    ): void {
        foreach ($requestArguments as $argumentKey => $argumentValue) {
            if (!is_array($argumentValue)) {
                continue;
            }

            $originalContainer = $this->getRepeatableContainerByOriginalIdentifier($argumentKey);
            $argumentPath[] = $argumentKey;
            $copyIndexes = array_keys($argumentValue);

            if ($originalContainer instanceof RepeatableContainerInterface
                && [] === array_filter(array_keys($copyIndexes), is_string(...)) // @phpstan-ignore identical.alwaysTrue
            ) {
                $this->pruneDeletedContainerValues(implode('.', $argumentPath), $copyIndexes);
            }

            $this->removeDeletedRepeatableContainersFromFormValuesByRequest($argumentValue, $argumentPath);
            array_pop($argumentPath);
        }
    }

    /**
     * This function fetches variants of the original form element and copies them into the
     * new form element.
     * Extendable by listening for @see CopyVariantEvent.
     */
    protected function copyVariants(
        FormElementInterface $originalFormElement,
        FormElementInterface $newFormElement,
        string $newIdentifier,
    ): void {
        if (!$this->features->isFeatureEnabled('repeatableFormElements.copyVariants')) {
            return;
        }

        $originalVariants = $originalFormElement->getVariants(); // @phpstan-ignore method.notFound
        foreach ($originalVariants as $originalIdentifier => $originalVariant) {
            // make sure that we only copy variants that are missing in the copied element
            if ($originalVariant instanceof RenderableVariant
                && !in_array($originalIdentifier, array_keys($newFormElement->getVariants()), true) // @phpstan-ignore method.notFound
            ) {
                // variant properties are protected and class is marked internal,
                // so we use reflection
                $reflectionClass = new ReflectionClass(RenderableVariant::class);
                $propOption = $reflectionClass->getProperty('options');
                $propCondition = $reflectionClass->getProperty('condition');
                $options = $propOption->getValue($originalVariant);
                $options['condition'] = $propCondition->getValue($originalVariant);
                $options['identifier'] = $originalIdentifier;

                /** @var CopyVariantEvent $event */
                $event = $this->eventDispatcher->dispatch(
                    new CopyVariantEvent($options, $originalFormElement, $newFormElement, $newIdentifier),
                );

                // only add this variant, if it did not get disabled.
                if (!$event->isVariantEnabled()) {
                    continue;
                }

                $options = $event->getOptions();
                $newFormElement->createVariant($options); // @phpstan-ignore method.notFound
            }
        }
    }

    /**
     * @param array<int, int|string> $copyIndexes
     */
    private function pruneDeletedContainerValues(string $formValuePath, array $copyIndexes): void
    {
        $formValue = $this->formState->getFormValue($formValuePath);
        if (!is_array($formValue)) {
            return;
        }

        foreach ($formValue as $key => $_) {
            if (!in_array($key, $copyIndexes, true)) {
                unset($formValue[$key]);
            }
        }
        $this->formState->setFormValue($formValuePath, $formValue);
    }

    /**
     * @param array<int, int|string> $copyIndexes
     * @param array<int, string|int> $argumentPath
     */
    private function processCopyIndexes(
        RepeatableContainerInterface $originalContainer,
        array $copyIndexes,
        array $argumentPath,
    ): void {
        $copyIndexes = ArrayUtility::sortArrayWithIntegerKeys($copyIndexes);

        $referenceContainer = $this->resolveReferenceContainer($originalContainer, $argumentPath);
        if (!$referenceContainer instanceof RepeatableContainerInterface) {
            return;
        }

        $firstReferenceContainer = $referenceContainer;
        $firstReferenceContainer->setRenderingOption('_isReferenceContainer', true); // @phpstan-ignore method.notFound
        $firstReferenceContainer->setRenderingOption('_copyMother', $originalContainer->getIdentifier()); // @phpstan-ignore method.notFound

        $minimumCopies = (int) ($firstReferenceContainer->getProperties()['minimumCopies'] ?? 0);
        $maximumCopies = (int) ($firstReferenceContainer->getProperties()['maximumCopies'] ?? 0);

        $copyNumber = 1;
        foreach ($copyIndexes as $copyIndex) {
            $contextPath = $argumentPath;
            $contextPath[] = $copyIndex;
            $newIdentifier = implode('.', $contextPath);

            $referenceContainer = $this->copyRepeatableContainer($originalContainer, $referenceContainer, $newIdentifier);
            $referenceContainer->setRenderingOption('_copyReference', $firstReferenceContainer->getIdentifier()); // @phpstan-ignore method.notFound

            if ($copyNumber > $maximumCopies) {
                $this->addError($referenceContainer, 1518701681, 'The maximum number of copies has been reached');
            }
            ++$copyNumber;
        }

        if ($copyNumber - 1 < $minimumCopies) {
            $this->addError($firstReferenceContainer, 1518701682, 'The minimum number of copies has not yet been reached');
        }
    }

    /**
     * @param array<int, string|int> $argumentPath
     */
    private function resolveReferenceContainer(
        RepeatableContainerInterface $originalContainer,
        array $argumentPath,
    ): ?RepeatableContainerInterface {
        if (count($argumentPath) <= 1) {
            return $originalContainer;
        }

        $referenceContainerPath = $argumentPath;
        $referenceContainerPath[] = 0;
        $referenceContainerIdentifier = implode('.', $referenceContainerPath);
        $element = $this->formDefinition->getElementByIdentifier($referenceContainerIdentifier);

        return $element instanceof RepeatableContainerInterface ? $element : null;
    }
}
