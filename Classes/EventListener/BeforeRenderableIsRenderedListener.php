<?php

declare(strict_types=1);

namespace TRITUM\RepeatableFormElements\EventListener;

use TRITUM\RepeatableFormElements\Hooks\FormHooks;
use TYPO3\CMS\Form\Event\BeforeRenderableIsRenderedEvent;

/**
 * PSR-14 replacement for the beforeRendering SC_OPTIONS hook.
 * This event listener is used in TYPO3 v14+ where the hook was removed.
 */
final class BeforeRenderableIsRenderedListener
{
    public function __construct(
        private readonly FormHooks $formHooks,
    ) {}

    public function __invoke(BeforeRenderableIsRenderedEvent $event): void
    {
        $this->formHooks->beforeRendering($event->formRuntime, $event->renderable);
    }
}
