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

/*
 * This file is part of the "repeatable_form_elements" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TRITUM\RepeatableFormElements\Event\CopyVariantEvent;

/**
 * AdaptVariantConditionEventListener.
 *
 * @param CopyVariantEvent $event
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
final class AdaptVariantConditionEventListener
{
    public function __invoke(CopyVariantEvent $event): void
    {
        $options = $event->getOptions();
        $originalIdentifier = $event->getOriginalFormElement()->getIdentifier();

        // get path strings for identifiers for replacement in condition
        // e.g. for `traverse(formValues, 'repeatablecontainer-1.0.checkbox-1')`
        $originalIdentifierAsPath = str_replace('.', '/', $originalIdentifier);
        $newIdentifierAsPath = str_replace('.', '/', $event->getNewIdentifier());

        // adapt original condition to match identifier of the copied form element
        $options['condition'] = str_replace(
            [
                $originalIdentifier,
                $originalIdentifierAsPath,
            ],
            [
                $event->getNewIdentifier(),
                $newIdentifierAsPath,
            ],
            $options['condition'],
        );

        $event->setOptions($options);
    }
}
