<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ws_bulletinboard/Configuration/TSconfig/ContentElementWizard.tsconfig">'
);
