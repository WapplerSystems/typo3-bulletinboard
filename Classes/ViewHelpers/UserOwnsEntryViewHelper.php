<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace WapplerSystems\WsBulletinboard\ViewHelpers;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use WapplerSystems\WsBulletinboard\Domain\Model\Entry;

class UserOwnsEntryViewHelper extends AbstractConditionViewHelper
{
    /**
     *
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('entry', Entry::class, '', true);
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexibility in overriding this method.
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        /** @var Entry $entry */
        $entry = $arguments['entry'];
        /** @var UserAspect $userAspect */
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');
        if (!$userAspect->isLoggedIn()) {
            return false;
        }
        if ($entry->getFeUser() === null) {
            return false;
        }

        return $entry->getFeUser()->getUid() === $userAspect->get('id');
    }
}
