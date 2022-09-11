<?php
defined('TYPO3_MODE') || die('Access denied.');


call_user_func(function () {
// Adding fields to the tt_content table definition in TCA
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'ws_bulletinboard',
        'Configuration/TypoScript',
        'WapplerSystems Bulletinboard'
    );
});
