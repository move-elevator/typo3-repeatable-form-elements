<?php

declare(strict_types=1);

namespace TRITUM\RepeatableFormElements\Event;

use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Dispatched after a form renderable has been built/copied by the repeatable container logic.
 *
 * This event replaces the former SC_OPTIONS hook
 * $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished']
 * which was removed in TYPO3 v14 (Breaking #98239).
 */
final class AfterBuildingFinishedEvent
{
    public function __construct(
        public readonly RenderableInterface $renderable,
    ) {}
}
