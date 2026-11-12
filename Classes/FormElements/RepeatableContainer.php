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

namespace TRITUM\RepeatableFormElements\FormElements;

/*
 * This file is part of the "repeatable_form_elements" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;

/**
 * RepeatableContainer.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 * @license GPL-2.0-or-later
 */
class RepeatableContainer extends Section implements RepeatableContainerInterface {}
