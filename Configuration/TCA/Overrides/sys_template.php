<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');


call_user_func(function () {
// Adding fields to the tt_content table definition in TCA
    ExtensionManagementUtility::addStaticFile(
        'ws_bulletinboard',
        'Configuration/TypoScript',
        'WapplerSystems bulletin board'
    );
});
