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

namespace TRITUM\RepeatableFormElements\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\{CoversClass, Test};
use PHPUnit\Framework\TestCase;
use TRITUM\RepeatableFormElements\Event\CopyVariantEvent;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * CopyVariantEventTest.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
#[CoversClass(CopyVariantEvent::class)]
final class CopyVariantEventTest extends TestCase
{
    private CopyVariantEvent $subject;
    private FormElementInterface $originalFormElement;
    private FormElementInterface $newFormElement;

    protected function setUp(): void
    {
        $this->originalFormElement = self::createStub(FormElementInterface::class);
        $this->newFormElement = self::createStub(FormElementInterface::class);

        $this->subject = new CopyVariantEvent(
            ['condition' => 'true', 'identifier' => 'variant-1'],
            $this->originalFormElement,
            $this->newFormElement,
            'container.1.field-1',
        );
    }

    #[Test]
    public function constructorSetsAllProperties(): void
    {
        self::assertSame(['condition' => 'true', 'identifier' => 'variant-1'], $this->subject->getOptions());
        self::assertSame($this->originalFormElement, $this->subject->getOriginalFormElement());
        self::assertSame($this->newFormElement, $this->subject->getNewFormElement());
        self::assertSame('container.1.field-1', $this->subject->getNewIdentifier());
    }

    #[Test]
    public function variantIsEnabledByDefault(): void
    {
        self::assertTrue($this->subject->isVariantEnabled());
    }

    #[Test]
    public function setVariantEnabledChangesState(): void
    {
        $this->subject->setVariantEnabled(false);

        self::assertFalse($this->subject->isVariantEnabled());
    }

    #[Test]
    public function setOptionsReplacesOptions(): void
    {
        $newOptions = ['condition' => 'false', 'identifier' => 'variant-2'];

        $this->subject->setOptions($newOptions);

        self::assertSame($newOptions, $this->subject->getOptions());
    }
}
