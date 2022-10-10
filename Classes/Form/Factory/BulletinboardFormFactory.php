<?php
declare(strict_types = 1);

namespace WapplerSystems\WsBulletinboard\Form\Factory;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\GridRow;
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WapplerSystems\WsBulletinboard\Exception\MissingConfigurationException;

class BulletinboardFormFactory extends AbstractFormFactory
{


    /**
     * @param array $configuration
     * @param string|null $prototypeName
     * @return FormDefinition
     * @throws \TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException
     * @throws \TYPO3\CMS\Form\Domain\Configuration\Exception\PrototypeNotFoundException
     * @throws \TYPO3\CMS\Form\Domain\Exception\TypeDefinitionNotFoundException
     * @throws \TYPO3\CMS\Form\Domain\Exception\TypeDefinitionNotValidException
     * @throws \TYPO3\CMS\Form\Domain\Model\Exception\FinisherPresetNotFoundException
     * @throws MissingConfigurationException
     */
    public function build(array $configuration, string $prototypeName = null): FormDefinition
    {

        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('bulletinboard');

        $formDefinition = GeneralUtility::makeInstance(FormDefinition::class, 'bulletinboardEntryForm', $prototypeConfiguration);
        $formDefinition->setRendererClassName(FluidFormRenderer::class);
        $formDefinition->setRenderingOption('controllerAction', 'new');
        $formDefinition->setRenderingOption('submitButtonLabel', 'Submit');


        if (empty($configuration['frameworkConfiguration']['persistence']['storagePid'])) {
            throw new MissingConfigurationException('No storagePid set', 1627843908);
        }

        $actionKey = GeneralUtility::makeInstance(Random::class)->generateRandomHexString(30);


        $context = GeneralUtility::makeInstance(Context::class);

        $saveToDatabaseFinisher = $formDefinition->createFinisher('SaveToDatabase');
        $saveToDatabaseFinisher->setOptions([
            'table' => 'tx_wsbulletinboard_domain_model_entry',
            'mode' => 'insert',
            'databaseColumnMappings' => [
                'pid' => [
                    'value' => $configuration['frameworkConfiguration']['persistence']['storagePid'],
                ],
                'tstamp' => [
                    'value' => time(),
                ],
                'crdate' => [
                    'value' => time(),
                ],
                'action_key' => [
                    'value' => $actionKey,
                ],
                'hidden' => [
                    'value' => ($configuration['automaticApproval'] === '1') ? 0 : 1,
                ],
                'fe_user' => [
                    'value' => $context->getPropertyFromAspect('frontend.user', 'id'),
                ],
            ],

            'elements' => [
                'title' => [
                    'mapOnDatabaseColumn' => 'title',
                ],
                'name' => [
                    'mapOnDatabaseColumn' => 'name',
                ],
                'image' => [
                    'mapOnDatabaseColumn' => 'image',
                ],
                'message' => [
                    'mapOnDatabaseColumn' => 'message',
                ],
            ]
        ]);

        $recipients = [];
        $recipientsFlexform = $configuration['verification']['recipients'];
        foreach ($recipientsFlexform as $recipient) {
            $recipients[$recipient['container']['address']] = $recipient['container']['name'];
        }

        if (count($recipients) === 0) {
            throw new MissingConfigurationException('No recipients set', 1627843942);
        }

        $defaultFrom = MailUtility::getSystemFrom();
        if (isset($defaultFrom[0])) {
            $defaultFrom = [$defaultFrom[0] => 'no sendername'];
        }

        if (!empty($configuration['verification']['email']['senderEmailAddress'])) {
            $defaultFrom = [$configuration['verification']['email']['senderEmailAddress'] => $configuration['verification']['email']['senderName']];
        }

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $confirmationUrl = $uriBuilder->reset()
            ->setTargetPageUid($configuration['pageUid'])
            ->setCreateAbsoluteUri(true)
            ->setArguments([
                'tx_wsbulletinboard_form' => [
                    'action' => 'confirm',
                    'controller' => 'Bulletinboard',
                    'action_key' => $actionKey,
                ],
            ])
            ->buildFrontendUri();

        $declineUrl = $uriBuilder->reset()
            ->setTargetPageUid($configuration['pageUid'])
            ->setCreateAbsoluteUri(true)
            ->setArguments([
                'tx_wsbulletinboard_form' => [
                    'action' => 'decline',
                    'controller' => 'Bulletinboard',
                    'action_key' => $actionKey,
                ],
            ])
            ->buildFrontendUri();

        $emailFinisher = $formDefinition->createFinisher('EmailToReceiver');
        $emailFinisher->setOptions([
            'subject' => $configuration['verification']['email']['subject'],
            'recipients' => $recipients,
            'senderName' => $defaultFrom[array_key_first($defaultFrom)],
            'senderAddress' => array_key_first($defaultFrom),
            'useFluidEmail' => true,
            'templateName' => 'Notification',
            'templateRootPaths' => [
                50 => 'EXT:ws_bulletinboard/Resources/Private/Templates/Email/',
            ],
            'variables' => [
                'confirmationUrl' => $confirmationUrl,
                'declineUrl' => $declineUrl,
            ]
        ]);


        $redirectFinisher = $formDefinition->createFinisher('Redirect');
        $redirectFinisher->setOptions([
            'pageUid' => $configuration['pageUid'],
            'additionalParameters' => 'tx_wsbulletinboard_form[action]=done',
        ]);

        $page = $formDefinition->createPage('page1');

        /** @var GridRow $row */
        $row = $page->createElement('row1', 'GridRow');

        /** @var Section $fieldset */
        $fieldset = $row->createElement('fieldsetEntry', 'Fieldset');
        $fieldset->setLabel('New Bulletinboard Entry');
        $fieldset->setOptions(['properties' => [
            'gridColumnClassAutoConfiguration' => [
                'viewPorts' => [
                    'md' => 12
                ]
            ]
        ]]);

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('title', 'Text');
        $element->setLabel('Title');
        $element->setProperty('required', true);
        $element->addValidator(new StringLengthValidator(['maximum' => 500]));
        $element->addValidator(new NotEmptyValidator());

        $element = $fieldset->createElement('image', 'ImageUpload');
        $element->setLabel('Image');

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('message', 'Textarea');
        $element->setLabel('Message');
        $element->setProperty('rows', '4');
        $element->setProperty('elementClassAttribute', 'form-control-bstextcounter');
        $element->setProperty('fluidAdditionalAttributes', ['data-maximum-chars' => (int)$configuration['fields']['message']['maxCharacters']]);
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(new StringLengthValidator(['minimum' => 50, 'maximum' => (int)$configuration['fields']['message']['maxCharacters']]));



        $this->triggerFormBuildingFinished($formDefinition);

        return $formDefinition;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}
