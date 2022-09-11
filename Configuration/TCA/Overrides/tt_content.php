<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'WapplerSystems.ws_bulletinboard',
        'List',
        'LLL:EXT:ws_bulletinboard/Resources/Private/Language/locallang_db.xlf:wsbulletinboard_list'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'WapplerSystems.ws_bulletinboard',
        'Form',
        'LLL:EXT:ws_bulletinboard/Resources/Private/Language/locallang_db.xlf:wsbulletinboard_form'
    );

    /* Flexform setting  */
    $pluginSignatureform = 'wsbulletinboard_form';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_bulletinboard/Configuration/FlexForm/form.xml');

    $pluginSignatureform = 'wsbulletinboard_list';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_bulletinboard/Configuration/FlexForm/list.xml');

});
