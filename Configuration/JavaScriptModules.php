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

return [
    'dependencies' => ['form'],
    'imports' => [
        '@tritum/repeatable-form-elements/' => 'EXT:repeatable_form_elements/Resources/Public/JavaScript/',
    ],
];
