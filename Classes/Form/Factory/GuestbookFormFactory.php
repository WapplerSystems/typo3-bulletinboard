<?php
declare(strict_types = 1);

namespace WapplerSystems\WsBulletinboard\Form\Factory;

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\GridRow;
use TYPO3\CMS\Form\Domain\Model\FormElements\Section;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
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
                    'value' => 1,
                ],
            ],

            'elements' => [
                'name' => [
                    'mapOnDatabaseColumn' => 'name',
                ],
                'email' => [
                    'mapOnDatabaseColumn' => 'email',
                ],
                'city' => [
                    'mapOnDatabaseColumn' => 'city',
                ],
                'website' => [
                    'mapOnDatabaseColumn' => 'website',
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
        $element = $fieldset->createElement('name', 'Text');
        $element->setLabel('Name');
        $element->setProperty('required', true);
        $element->addValidator(new StringLengthValidator(['maximum' => 50]));
        $element->addValidator(new NotEmptyValidator());

        if ($configuration['fields']['email']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('email', 'Text');
            $element->setLabel('E-Mail');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'mail@mail.de']);
            $element->addValidator(new EmailAddressValidator());
            if ($$configuration['fields']['email']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ($configuration['fields']['website']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('website', 'Text');
            $element->setLabel('Website');
            $element->setProperty('fluidAdditionalAttributes', ['placeholder' => 'https://www.website.de']);
            $element->addValidator(new StringLengthValidator(['maximum' => 200]));
            if ($configuration['fields']['website']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        if ($configuration['fields']['city']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('city', 'Text');
            $element->setLabel('City');
            $element->addValidator(new StringLengthValidator(['maximum' => 100]));
            if ($configuration['fields']['city']['mandatory'] === '1') {
                $element->addValidator(new NotEmptyValidator());
            }
        }

        /** @var GenericFormElement $element */
        $element = $fieldset->createElement('message', 'Textarea');
        $element->setLabel('Message');
        $element->setProperty('rows', '4');
        $element->setProperty('elementClassAttribute', 'form-control-bstextcounter');
        $element->setProperty('fluidAdditionalAttributes', ['data-maximum-chars' => (int)$configuration['fields']['message']['maxCharacters']]);
        $element->addValidator(new NotEmptyValidator());
        $element->addValidator(new StringLengthValidator(['minimum' => 50, 'maximum' => (int)$configuration['fields']['message']['maxCharacters']]));

        if ($configuration['fields']['captcha']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('captcha', 'Captcha');
            $element->setLabel('Captcha');
        }

        if ($configuration['fields']['privacyPolicy']['enable'] === '1') {
            /** @var GenericFormElement $element */
            $element = $fieldset->createElement('privacyPolicy', 'PrivacyPolicyCheckbox');
            $element->setLabel('I agree to the privacy policy');
            $element->setProperty('privacyPolicyUid', $configuration['fields']['privacyPolicy']['page'] ?? '');
            $element->addValidator(new NotEmptyValidator());
        }

        return $formDefinition;
    }

}
