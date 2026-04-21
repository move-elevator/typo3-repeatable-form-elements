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

namespace TRITUM\RepeatableFormElements\Hooks;

/*
 * This file is part of the "repeatable_form_elements" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use Psr\EventDispatcher\EventDispatcherInterface;
use TRITUM\RepeatableFormElements\Event\AfterBuildingFinishedEvent;
use TRITUM\RepeatableFormElements\FormElements\RepeatableContainerInterface;
use TRITUM\RepeatableFormElements\Service\CopyService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Form\Domain\Model\Exception\DuplicateFormElementException;
use TYPO3\CMS\Form\Domain\Model\FormElements\{AbstractFormElement, FormElementInterface};
use TYPO3\CMS\Form\Domain\Model\Renderable\{AbstractRenderable, CompositeRenderableInterface, RenderableInterface, RootRenderableInterface};
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * FormHooks.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
final class FormHooks
{
    /**
     * @param array<string, mixed> $rawRequestArguments
     *
     * @throws DuplicateFormElementException
     */
    public function afterInitializeCurrentPage(
        FormRuntime $formRuntime,
        ?CompositeRenderableInterface $currentPage = null,
        ?CompositeRenderableInterface $lastPage = null,
        array $rawRequestArguments = [],
    ): ?CompositeRenderableInterface {
        foreach ($formRuntime->getPages() as $page) {
            $this->setRootRepeatableContainerIdentifiers($page, $formRuntime);
        }

        // first request
        if (null === $lastPage) {
            return $currentPage;
        }

        $copyService = GeneralUtility::makeInstance(CopyService::class, $formRuntime);
        if ($this->userWentBackToPreviousStep($formRuntime, $currentPage, $lastPage)) {
            $copyService->createCopiesFromFormState();
        } else {
            $copyService->createCopiesFromCurrentRequest();
        }

        return $currentPage;
    }

    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        if ($renderable instanceof FormElementInterface) {
            $properties = $renderable->getProperties();

            $fluidAdditionalAttributes = $properties['fluidAdditionalAttributes'] ?? [];
            $fluidAdditionalAttributes['data-element-type'] = $renderable->getType();
            if ('DatePicker' === $renderable->getType()) {
                $fluidAdditionalAttributes['data-element-datepicker-enabled'] = (int) $renderable->getProperties()['enableDatePicker'];
                $fluidAdditionalAttributes['data-element-datepicker-date-format'] = $renderable->getProperties()['dateFormat'];
            }

            $renderable->setProperty('fluidAdditionalAttributes', $fluidAdditionalAttributes);
        }
    }

    /**
     * @param array<int, string> $repeatableContainerIdentifiers
     *
     * @throws DuplicateFormElementException
     */
    private function setRootRepeatableContainerIdentifiers(
        RenderableInterface $renderable,
        FormRuntime $formRuntime,
        array $repeatableContainerIdentifiers = [],
    ): void {
        $isRepeatableContainer = $renderable instanceof RepeatableContainerInterface;

        $hasOriginalIdentifier = isset($renderable->getRenderingOptions()['_originalIdentifier']);
        if ($isRepeatableContainer) {
            $repeatableContainerIdentifiers[] = $renderable->getIdentifier();
            if (!$hasOriginalIdentifier) {
                $renderable->setRenderingOption('_isRootRepeatableContainer', true); // @phpstan-ignore method.notFound
                $renderable->setRenderingOption('_isReferenceContainer', true); // @phpstan-ignore method.notFound
            }
        }

        if ([] !== $repeatableContainerIdentifiers && !$hasOriginalIdentifier) {
            $this->rewriteRenderableIdentifier($renderable, $formRuntime, $repeatableContainerIdentifiers, $isRepeatableContainer);
        }

        if ($renderable instanceof RepeatableContainerInterface) {
            foreach ($renderable->getElements() as $childRenderable) {
                $this->setRootRepeatableContainerIdentifiers($childRenderable, $formRuntime, $repeatableContainerIdentifiers);
            }
        }
    }

    /**
     * @param array<int, string> $repeatableContainerIdentifiers
     *
     * @throws DuplicateFormElementException
     */
    private function rewriteRenderableIdentifier(
        RenderableInterface $renderable,
        FormRuntime $formRuntime,
        array $repeatableContainerIdentifiers,
        bool $isRepeatableContainer,
    ): void {
        if (!$renderable instanceof AbstractRenderable) {
            return;
        }

        $newIdentifier = implode('.0.', $repeatableContainerIdentifiers).'.0';
        if (!$isRepeatableContainer) {
            $newIdentifier .= '.'.$renderable->getIdentifier();
        }
        $originalIdentifier = $renderable->getIdentifier();
        $renderable->setRenderingOption('_originalIdentifier', $originalIdentifier);

        if ($renderable instanceof AbstractFormElement && null !== $renderable->getDefaultValue()) {
            $formRuntime->getFormDefinition()->addElementDefaultValue($newIdentifier, $renderable->getDefaultValue());
        }

        $formRuntime->getFormDefinition()->unregisterRenderable($renderable);
        $renderable->setIdentifier($newIdentifier);
        $formRuntime->getFormDefinition()->registerRenderable($renderable);

        $copyService = GeneralUtility::makeInstance(CopyService::class, $formRuntime);
        [$originalProcessingRule] = $copyService->copyProcessingRule($originalIdentifier, $newIdentifier);

        if ($renderable instanceof FormElementInterface) {
            /** @var ValidatorInterface $validator */
            foreach ($originalProcessingRule->getValidators() as $validator) {
                $renderable->addValidator($validator);
            }
        }

        // Legacy hook (v13 compat, no-op in v14)
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'] ?? [] as $className) {
            $hookObj = GeneralUtility::makeInstance($className); // @phpstan-ignore argument.templateType
            if (method_exists($hookObj, 'afterBuildingFinished')) {
                $hookObj->afterBuildingFinished($renderable);
            }
        }
        // PSR-14 event (v13 + v14)
        GeneralUtility::makeInstance(EventDispatcherInterface::class)->dispatch(
            new AfterBuildingFinishedEvent($renderable),
        );
    }

    /**
     * returns TRUE if the user went back to any previous step in the form.
     */
    private function userWentBackToPreviousStep(
        FormRuntime $formRuntime,
        ?CompositeRenderableInterface $currentPage = null,
        ?CompositeRenderableInterface $lastPage = null,
    ): bool {
        return null !== $currentPage
                && null !== $lastPage
                && $currentPage->getIndex() < $lastPage->getIndex();
    }
}
