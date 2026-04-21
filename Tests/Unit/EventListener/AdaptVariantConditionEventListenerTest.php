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

namespace TRITUM\RepeatableFormElements\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\{CoversClass, Test};
use PHPUnit\Framework\TestCase;
use TRITUM\RepeatableFormElements\Event\CopyVariantEvent;
use TRITUM\RepeatableFormElements\EventListener\AdaptVariantConditionEventListener;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * AdaptVariantConditionEventListenerTest.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
#[CoversClass(AdaptVariantConditionEventListener::class)]
final class AdaptVariantConditionEventListenerTest extends TestCase
{
    private AdaptVariantConditionEventListener $subject;

    protected function setUp(): void
    {
        $this->subject = new AdaptVariantConditionEventListener();
    }

    #[Test]
    public function conditionIdentifierIsReplacedWithNewIdentifier(): void
    {
        $originalElement = self::createStub(FormElementInterface::class);
        $originalElement->method('getIdentifier')->willReturn('repeatablecontainer-1.0.checkbox-1');

        $event = new CopyVariantEvent(
            ['condition' => "traverse(formValues, 'repeatablecontainer-1.0.checkbox-1') == 1"],
            $originalElement,
            self::createStub(FormElementInterface::class),
            'repeatablecontainer-1.1.checkbox-1',
        );

        ($this->subject)($event);

        self::assertSame(
            "traverse(formValues, 'repeatablecontainer-1.1.checkbox-1') == 1",
            $event->getOptions()['condition'],
        );
    }

    #[Test]
    public function pathStyleIdentifierIsAlsoReplaced(): void
    {
        $originalElement = self::createStub(FormElementInterface::class);
        $originalElement->method('getIdentifier')->willReturn('container.0.field-1');

        $event = new CopyVariantEvent(
            ['condition' => "traverse(formValues, 'container/0/field-1') == 'yes'"],
            $originalElement,
            self::createStub(FormElementInterface::class),
            'container.1.field-1',
        );

        ($this->subject)($event);

        self::assertSame(
            "traverse(formValues, 'container/1/field-1') == 'yes'",
            $event->getOptions()['condition'],
        );
    }

    #[Test]
    public function conditionWithoutMatchingIdentifierRemainsUnchanged(): void
    {
        $originalElement = self::createStub(FormElementInterface::class);
        $originalElement->method('getIdentifier')->willReturn('container.0.field-1');

        $condition = "traverse(formValues, 'unrelated-field') == 1";
        $event = new CopyVariantEvent(
            ['condition' => $condition],
            $originalElement,
            self::createStub(FormElementInterface::class),
            'container.1.field-1',
        );

        ($this->subject)($event);

        self::assertSame($condition, $event->getOptions()['condition']);
    }

    #[Test]
    public function otherOptionsArePreserved(): void
    {
        $originalElement = self::createStub(FormElementInterface::class);
        $originalElement->method('getIdentifier')->willReturn('container.0.field-1');

        $event = new CopyVariantEvent(
            [
                'condition' => "traverse(formValues, 'container.0.field-1') == 1",
                'identifier' => 'variant-1',
                'properties' => ['fluidAdditionalAttributes' => ['class' => 'hidden']],
            ],
            $originalElement,
            self::createStub(FormElementInterface::class),
            'container.1.field-1',
        );

        ($this->subject)($event);

        $options = $event->getOptions();
        self::assertSame('variant-1', $options['identifier']);
        self::assertSame(['fluidAdditionalAttributes' => ['class' => 'hidden']], $options['properties']);
    }
}
