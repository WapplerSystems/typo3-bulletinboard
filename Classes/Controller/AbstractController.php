<?php

namespace WapplerSystems\WsBulletinboard\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SysLog\Action as SystemLogGenericAction;
use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;
use TYPO3\CMS\Core\SysLog\Type as SystemLogType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use WapplerSystems\WsBulletinboard\Exception\MissingConfigurationException;
use WapplerSystems\WsBulletinboard\View\ErrorView;

class AbstractController extends ActionController implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected function callActionMethod(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::callActionMethod($request);
        } catch (MissingConfigurationException $exception) {

            /** @var ErrorView $view */
            $view = GeneralUtility::makeInstance(ErrorView::class);
            $view->assignMultiple(['errorCode' => $exception->getCode(), 'errorMessage' => $exception->getMessage()]);
            if (method_exists($view, 'injectSettings')) {
                $view->injectSettings($this->settings);
            }

            $request = $GLOBALS['TYPO3_REQUEST'];
            $normalizedParams = $request->getAttribute('normalizedParams');
            $requestUrl = $normalizedParams->getRequestUrl();

            $errorMessage = "ws_bulletinboard; " . $exception->getCode() . "; " . $exception->getMessage() . "; " . $requestUrl;
            $this->logger->error($errorMessage);

            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['belogErrorReporting']) {
                try {
                    $this->writeLog($errorMessage, 2);
                } catch (\Exception $e) {
                }
            }

            return $this->htmlResponse($view->render())->withStatus(500);

        }
    }


    /**
     * Writes an error in the sys_log table
     *
     * @param string $logMessage Default text that follows the message (in english!).
     * @param int $severity The error level of the message (0 = OK, 1 = warning, 2 = error)
     */
    protected function writeLog($logMessage, $severity)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_log');
        if ($connection->isConnected()) {
            $userId = 0;
            $workspace = 0;
            $data = [];
            $backendUser = $this->getBackendUser();
            if (is_object($backendUser)) {
                if (isset($backendUser->user['uid'])) {
                    $userId = $backendUser->user['uid'];
                }
                if (isset($backendUser->workspace)) {
                    $workspace = $backendUser->workspace;
                }
                if (!empty($backendUser->user['ses_backuserid'])) {
                    $data['originalUser'] = $backendUser->user['ses_backuserid'];
                }
            }

            $connection->insert(
                'sys_log',
                [
                    'userid' => $userId,
                    'type' => SystemLogType::ERROR,
                    'action' => SystemLogGenericAction::UNDEFINED,
                    'error' => SystemLogErrorClassification::SYSTEM_ERROR,
                    'level' => $severity,
                    'details_nr' => 0,
                    'details' => str_replace('%', '%%', $logMessage),
                    'log_data' => empty($data) ? '' : serialize($data),
                    'IP' => (string)GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                    'tstamp' => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('date', 'timestamp'),
                    'workspace' => $workspace
                ]
            );
        }
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

}
