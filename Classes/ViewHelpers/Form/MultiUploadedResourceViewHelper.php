<?php

declare(strict_types=1);

namespace WapplerSystems\WsBulletinboard\ViewHelpers\Form;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * Extends UploadedResourceViewHelper with resources previously uploaded (UploadedResourceViewHelper supports a single file only).
 *
 * Scope: frontend
 */
final class MultiUploadedResourceViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    protected HashService $hashService;
    protected PropertyMapper $propertyMapper;

    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    public function injectPropertyMapper(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', '');
        $this->registerArgument('accept', 'array', 'Values for the accept attribute', false, []);
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerTagAttribute('multiple', 'string', 'Specifies that the file input element should allow multiple selection of files');
        $this->registerUniversalTagAttributes();
    }

    public function render(): string
    {
        $output = '';

        $name = $this->getName();
        $as = $this->arguments['as'];
        $accept = $this->arguments['accept'];

        if (!empty($accept)) {
            $this->tag->addAttribute('accept', implode(',', $accept));
        }

        if (isset($this->arguments['multiple'])) {
            $this->tag->addAttribute('name', $name . '[]');

            $resources = $this->getUploadedResources();

            if ($resources !== null) {
                $result = [];

                foreach ($resources as $i => $resource) {
                    $resourcePointerIdAttribute = '';
                    if ($this->hasArgument('id')) {
                        $resourcePointerIdAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-file-reference-' . ($i+1) . '"';
                    }
                    $resourcePointerValue = $resource->getUid();
                    if ($resourcePointerValue === null) {
                        // Newly created file reference which is not persisted yet.
                        // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                        $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
                    }
                    $result[] = $resource;
                    $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer][]" value="' .
                        htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue)) . '" data-index="' . ($i+1) . '" ' . $resourcePointerIdAttribute . ' />';
                }

                $this->templateVariableContainer->add($as, $result);
                $this->templateVariableContainer->add('isMultiResource', true);

                $output .= $this->renderChildren();
                $this->templateVariableContainer->remove($as);
            }
        } else {
            $this->tag->addAttribute('name', $name);

            $resource = $this->getUploadedResource();

            if ($resource !== null) {
                $resourcePointerIdAttribute = '';
                if ($this->hasArgument('id')) {
                    $resourcePointerIdAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-file-reference"';
                }
                $resourcePointerValue = $resource->getUid();
                if ($resourcePointerValue === null) {
                    // Newly created file reference which is not persisted yet.
                    // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                    $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
                }
                $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer]" value="' . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue)) . '"' . $resourcePointerIdAttribute . ' />';

                $this->templateVariableContainer->add($as, $resource);
                $output .= $this->renderChildren();
                $this->templateVariableContainer->remove($as);
            }
        }

        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $fieldName) {
            $this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']');
        }
        $this->tag->addAttribute('type', 'file');

        $this->setErrorClassAttribute();
        $output .= $this->tag->render();

        return $output;
    }

    /**
     * Return a previously uploaded resource.
     * Return NULL if errors occurred during property mapping for this property.
     */
    protected function getUploadedResource(): ?FileReference
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $resource = $this->getValueAttribute();
        if ($resource instanceof FileReference) {
            return $resource;
        }
        return $this->propertyMapper->convert($resource, FileReference::class);
    }

    /**
     * Return previously uploaded resources.
     * Return NULL if errors occurred during property mapping for this property.
     */
    protected function getUploadedResources(): ?array
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }

        $resources = $this->getValueAttribute();

        return array_map(function($resource) {
            if ($resource instanceof FileReference) {
                return $resource;
            }

            return $this->propertyMapper->convert($resource, FileReference::class);
        }, $resources ?: []);
    }
}
