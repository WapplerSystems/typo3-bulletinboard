<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

ExtensionManagementUtility::allowTableOnStandardPages('tx_wsbulletinboard_domain_model_entry');

ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ws_bulletinboard/Configuration/TSconfig/ContentElementWizard.tsconfig">'
);
