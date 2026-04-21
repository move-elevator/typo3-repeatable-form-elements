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
        ExtensionManagementUtility::addTypoScriptSetup(trim('
            plugin.tx_form {
                settings {
                    yamlConfigurations {
                        1511193633 = EXT:repeatable_form_elements/Configuration/Yaml/FormSetup.yaml
                    }
                }
            }
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
        // v13: these SC_OPTIONS hooks are the active mechanism.
        // v14: these hooks were removed (Breaking #107566, #107569).
        //      The PSR-14 event listeners in Configuration/Services.yaml take over.
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'][1511196413] = FormHooks::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1511196413] = FormHooks::class;
    }
}
