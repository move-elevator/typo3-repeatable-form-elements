<?php

declare(strict_types=1);

namespace TRITUM\RepeatableFormElements\EventListener;

use TRITUM\RepeatableFormElements\Hooks\FormHooks;
use TYPO3\CMS\Form\Event\AfterCurrentPageIsResolvedEvent;

/**
 * PSR-14 replacement for the afterInitializeCurrentPage SC_OPTIONS hook.
 * This event listener is used in TYPO3 v14+ where the hook was removed.
 */
final class AfterCurrentPageIsResolvedListener
{
    public function __construct(
        private readonly FormHooks $formHooks,
    ) {}

    public function __invoke(AfterCurrentPageIsResolvedEvent $event): void
    {
        $event->currentPage = $this->formHooks->afterInitializeCurrentPage(
            $event->formRuntime,
            $event->currentPage,
            $event->lastDisplayedPage,
            $event->request->getArguments(),
        );
    }
}
