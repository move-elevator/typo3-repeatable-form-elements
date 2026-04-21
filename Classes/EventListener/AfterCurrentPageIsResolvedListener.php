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

namespace TRITUM\RepeatableFormElements\EventListener;

use TRITUM\RepeatableFormElements\Hooks\FormHooks;
use TYPO3\CMS\Form\Event\AfterCurrentPageIsResolvedEvent;

/**
 * AfterCurrentPageIsResolvedListener.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
final readonly class AfterCurrentPageIsResolvedListener
{
    public function __construct(
        private FormHooks $formHooks,
    ) {}

    public function __invoke(AfterCurrentPageIsResolvedEvent $event): void
    {
        $event->currentPage = $this->formHooks->afterInitializeCurrentPage( // @phpstan-ignore assign.propertyType
            $event->formRuntime,
            $event->currentPage,
            $event->lastDisplayedPage,
            $event->request->getArguments(),
        );
    }
}
