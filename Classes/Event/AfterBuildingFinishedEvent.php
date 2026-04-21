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

use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * AfterBuildingFinishedEvent.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
final readonly class AfterBuildingFinishedEvent
{
    public function __construct(
        public RenderableInterface $renderable,
    ) {}
}
