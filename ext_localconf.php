<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use WapplerSystems\WsBulletinboard\Controller\BulletinboardController;
use WapplerSystems\WsBulletinboard\Hooks\FormElementCaptchaHook;

defined('TYPO3_MODE') or die();

ExtensionUtility::configurePlugin(
    'ws_bulletinboard',
    'List',
    [
        BulletinboardController::class => 'list',
    ],
    [
    ]
);

ExtensionUtility::configurePlugin(
    'ws_bulletinboard',
    'Form',
    [
        BulletinboardController::class => 'new,done,decline,confirm,entryNotFound',
    ],
    [
        BulletinboardController::class => 'new,done,decline,confirm,entryNotFound',
    ]
);


$icons = [
    'ext-ws-bulletinboard-icon' => 'ws_bulletinboard.svg',
];
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        SvgIconProvider::class,
        ['source' => 'EXT:ws_bulletinboard/Resources/Public/Icons/' . $path]
    );
}


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['ws_bulletinboard']= \WapplerSystems\WsBulletinboard\Hooks\PageLayoutView::class;

