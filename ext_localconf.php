<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_bulletinboard',
    'List',
    [
        \WapplerSystems\WsBulletinboard\Controller\BulletinboardController::class => 'list',
    ],
    [
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_bulletinboard',
    'Form',
    [
        \WapplerSystems\WsBulletinboard\Controller\BulletinboardController::class => 'new,done,decline,confirm,entryNotFound',
    ],
    [
        \WapplerSystems\WsBulletinboard\Controller\BulletinboardController::class => 'new,done,decline,confirm,entryNotFound',
    ]
);


$icons = [
    'ext-ws-bulletinboard-icon' => 'ws_bulletinboard.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:ws_bulletinboard/Resources/Public/Icons/' . $path]
    );
}


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['ws_bulletinboard']= \WapplerSystems\WsBulletinboard\Hooks\PageLayoutView::class;


if (!function_exists('gregwar_captcha_php_autoload') && !\TYPO3\CMS\Core\Core\Environment::isComposerMode()) {
    function gregwar_captcha_php_autoload($className)
    {
        $classPath = explode('\\', $className);
        if ($classPath[0] !== 'Gregwar') {
            return;
        }
        $path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ws_bulletinboard');

        $filePath = $path . DIRECTORY_SEPARATOR . 'Resources/Private/PHP/' . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }

    spl_autoload_register('gregwar_captcha_php_autoload');
}

// register cache table
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsbulletinboardcaptcha'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsbulletinboardcaptcha'] = [];
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1571076908]
    = \WapplerSystems\WsBulletinboard\Hooks\FormElementCaptchaHook::class;
