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
use TRITUM\RepeatableFormElements\Event\AfterBuildingFinishedEvent;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * AfterBuildingFinishedEventTest.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
#[CoversClass(AfterBuildingFinishedEvent::class)]
final class AfterBuildingFinishedEventTest extends TestCase
{
    #[Test]
    public function constructorExposesRenderable(): void
    {
        $renderable = self::createStub(RenderableInterface::class);

        $event = new AfterBuildingFinishedEvent($renderable);

        self::assertSame($renderable, $event->renderable);
    }
}
