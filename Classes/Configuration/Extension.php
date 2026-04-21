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

namespace TRITUM\RepeatableFormElements\Configuration;

use TRITUM\RepeatableFormElements\Hooks\FormHooks;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Extension.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 * @license GPL-2.0-or-later
 */
final class Extension
{
    public const KEY = 'repeatable_form_elements';

    public static function addTypoScriptSetup(): void
    {
        // @todo: maybe move this to 'EXT:repeatable_form_elements/ext_typoscript_setup.typoscript'
        ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1511193633 = EXT:repeatable_form_elements/Configuration/Yaml/FormSetup.yaml
                        1511193634 = EXT:repeatable_form_elements/Configuration/Yaml/FormSetupBackend.yaml
                    }
                }
            }
        '));
    }

    public static function registerHooks(): void
    {
        // These hooks are still supported in TYPO3 v13 and v14.
        // When TYPO3 introduces PSR-14 replacements, migrate to event listeners
        // registered in Configuration/Services.yaml.
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'][1511196413] = FormHooks::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1511196413] = FormHooks::class;
    }
}
