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

use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\CompositeRenderableInterface;

/**
 * RepeatableContainerInterface.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
interface RepeatableContainerInterface extends CompositeRenderableInterface
{
    /**
     * @return array<string, FormElementInterface>
     */
    public function getElements(): array;

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array;
}
