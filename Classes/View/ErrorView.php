<?php

namespace WapplerSystems\WsBulletinboard\View;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 */
class ErrorView
{
    /**
     * @var array
     */
    protected $variablesMarker = ['errorMessage' => 'ERROR_MESSAGE', 'errorCode' => 'ERROR_CODE'];

    protected $variables = [];

    /**
     * Renders the not found view
     *
     * @return string The rendered view
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception if no request has been set
     */
    public function render()
    {

        $template = file_get_contents($this->getTemplatePathAndFilename());
        $template = is_string($template) ? $template : '';
        $template = str_replace('###BASEURI###', GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), $template);

        foreach ($this->variablesMarker as $variableName => $marker) {
            $variableValue = $this->variables[$variableName] ?? '';
            $template = str_replace('###' . $marker . '###', $variableValue, $template);
        }
        return $template;
    }

    /**
     * Retrieves path and filename of the not-found-template
     *
     * @return string path and filename of the not-found-template
     */
    protected function getTemplatePathAndFilename()
    {
        return ExtensionManagementUtility::extPath('ws_bulletinboard') . 'Resources/Private/Templates/Error.html';
    }

    /**
     * A magic call method.
     *
     * Because this not found view is used as a Special Case in situations when no matching
     * view is available, it must be able to handle method calls which originally were
     * directed to another type of view. This magic method should prevent PHP from issuing
     * a fatal error.
     *
     * @param string $methodName
     * @param array $arguments
     */
    public function __call($methodName, array $arguments)
    {
    }


    /**
     * Add a variable to $this->viewData.
     * Can be chained, so $this->view->assign(..., ...)->assign(..., ...); is possible
     *
     * @param string $key Key of variable
     * @param mixed $value Value of object
     * @return ErrorView an instance of $this, to enable chaining
     */
    public function assign($key, $value)
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Add multiple variables to $this->viewData.
     *
     * @param array $values array in the format array(key1 => value1, key2 => value2).
     * @return ErrorView an instance of $this, to enable chaining
     */
    public function assignMultiple(array $values)
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }
        return $this;
    }
}
