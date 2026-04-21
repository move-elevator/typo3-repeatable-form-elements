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

defined('TYPO3') || exit;

call_user_func(static function (): void {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        TRITUM\RepeatableFormElements\Configuration\Extension::KEY,
        'Configuration/TypoScript',
        'Repeatable form configuration',
    );
});
