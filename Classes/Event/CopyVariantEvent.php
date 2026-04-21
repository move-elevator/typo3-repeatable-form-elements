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

namespace TRITUM\RepeatableFormElements\Event;

/*
 * This file is part of the "repeatable_form_elements" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * CopyVariantEvent.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
class CopyVariantEvent
{
    private readonly FormElementInterface $originalFormElement;
    private readonly FormElementInterface $newFormElement;
    private bool $variantEnabled = true;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private array $options,
        FormElementInterface $originalFormElement,
        FormElementInterface $newFormElement,
        private readonly string $newIdentifier,
    ) {
        $this->originalFormElement = $originalFormElement;
        $this->newFormElement = $newFormElement;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getOriginalFormElement(): FormElementInterface
    {
        return $this->originalFormElement;
    }

    public function getNewFormElement(): FormElementInterface
    {
        return $this->newFormElement;
    }

    public function getNewIdentifier(): string
    {
        return $this->newIdentifier;
    }

    public function isVariantEnabled(): bool
    {
        return $this->variantEnabled;
    }

    public function setVariantEnabled(bool $variantEnabled): void
    {
        $this->variantEnabled = $variantEnabled;
    }
}
