<?php
namespace WapplerSystems\WsBulletinboard\Hooks;

use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class PageLayoutView implements PageLayoutViewDrawItemHookInterface
{

    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row): void
    {
        $extKey = 'ws_bulletinboard';
        if ($row['CType'] === 'list' && ($row['list_type'] === 'wsbulletinboard_form' || $row['list_type'] === 'wsbulletinboard_list')) {
            $drawItem = false;
            $headerContent = '';
            // template
            $templateFilename = 'FormPreview';
            if ($row['list_type'] === 'wsbulletinboard_list') {
                $templateFilename = 'ListPreview';
            }

            $view = $this->getFluidTemplate($extKey, $templateFilename);

            if (!empty($row['pi_flexform'])) {
                $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            }

            // assign all to view
            $view->assignMultiple([
                //'data' => $row,
                'flexformData' => $flexFormService->convertFlexFormContentToArray($row['pi_flexform']),
            ]);

            // return the preview
            $itemContent = $parentObject->linkEditContent($view->render(), $row);
        }
    }

    /**
     * @param string $extKey
     * @param string $templateName
     * @return StandaloneView the fluid template
     */
    protected function getFluidTemplate($extKey, $templateName)
    {
        // prepare own template
        $fluidTemplateFile = GeneralUtility::getFileAbsFileName('EXT:' . $extKey . '/Resources/Private/Backend/' . $templateName . '.html');
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($fluidTemplateFile);
        return $view;
    }
}
