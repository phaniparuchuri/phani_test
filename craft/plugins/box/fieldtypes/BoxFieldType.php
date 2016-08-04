<?php

namespace Craft;

/**
 * Box Field Type.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class BoxFieldType extends AssetsFieldType
{
    /**
     * @var string The element type this field deals with.
     */
    protected $elementType = 'Box_File';

    /**
     * Get field type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Box files');
    }

    /**
     * Returns the label for the "Add" button.
     *
     * @return string
     */
    protected function getAddButtonLabel()
    {
        return Craft::t('Add a file');
    }

    /**
     * {@inheritdoc} IFieldType::getInputHtml()
     *
     * @param string $name
     * @param mixed  $criteria
     *
     * @return string
     */
    public function getInputHtml($name, $criteria)
    {
        craft()->templates->includeJsResource('box/box.js');

        $variables = $this->getInputTemplateVariables($name, $criteria);

        return craft()->templates->render($this->inputTemplate, $variables);
    }

    /**
     * {@inheritdoc} ISavableComponentType::getSettingsHtml()
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        return;
    }
}
