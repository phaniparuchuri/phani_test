<?php

namespace Craft;

/**
 * Box File Element Type.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_FileElementType extends BaseElementType
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Box File');
    }

    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }

    /**
     * Returns whether this element type has titles.
     *
     * @return bool
     */
    public function hasTitles()
    {
        return true;
    }

    /**
     * {@inheritdoc} IElementType::hasStatuses()
     *
     * @return bool
     */
    public function hasStatuses()
    {
        return true;
    }
    /**
     * {@inheritdoc} IElementType::getStatuses()
     *
     * @return array|null
     */
    public function getStatuses()
    {
        return array(
            Box_FileModel::READY => Craft::t('Ready'),
            Box_FileModel::PROCESSING => Craft::t('Processing'),
        );
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     *
     * @return array|false
     */
    public function getSources($context = null)
    {
        $sources = array(
            '*' => array(
                'label' => Craft::t('All files'),
                'data' => array('upload' => true),
            ),
        );

        foreach (craft()->box_fileType->getAllfileTypes() as $fileType) {
            $key = 'fileType:'.$fileType->id;

            $sources[$key] = array(
                'label' => $fileType->name,
                'criteria' => array('fileTypeId' => $fileType->id),
                'data' => array('upload' => true),
            );
        }

        return $sources;
    }

    /**
     * {@inheritdoc} IElementType::getAvailableActions()
     *
     * @param string|null $source
     *
     * @return array|null
     */
    public function getAvailableActions($source = null)
    {
        $actions = array();

        // Edit
        $editAction = craft()->elements->getAction('Edit');
        $editAction->setParams(array(
            'label' => Craft::t('Edit file'),
        ));
        $actions[] = $editAction;

        // Delete
        $deleteAction = craft()->elements->getAction('Box_DeleteBoxFile');
        $deleteAction->setParams(array(
            'label' => Craft::t('Delete file'),
        ));
        $actions[] = $deleteAction;

        return $actions;
    }

    /**
     * Returns the available attributes that can be shown/sorted by in table views.
     *
     * @return array
     */
    public function defineAvailableTableAttributes()
    {
        return array(
            'title' => array('label' => Craft::t('Title')),
            'fileVersion' => array('label' => Craft::t('Version')),
            'dateUpdated' => array('label' => Craft::t('Last edit')),
        );
    }

    /**
     * Returns the default table attributes.
     *
     * @param string $source
     *
     * @return array
     */
    public function getDefaultTableAttributes($source = null)
    {
        return array('title', 'fileVersion', 'dateUpdated');
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'fileType' => AttributeType::Mixed,
            'fileTypeId' => AttributeType::Mixed,
            'order' => array(AttributeType::String, 'default' => 'title'),
        );
    }

    /**
     * {@inheritdoc} IElementType::defineSortableAttributes()
     *
     * @return array
     */
    public function defineSortableAttributes($source = null)
    {
        return array(
            'dateUpdated desc, title' => Craft::t('Title'),
            'fileVersion' => Craft::t('Version'),
            'dateUpdated' => Craft::t('Last edit'),
        );
    }

    /**
     * Modifies an element query targeting elements of this type.
     *
     * @param DbCommand            $query
     * @param ElementCriteriaModel $criteria
     *
     * @return mixed
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
        $query
            ->addSelect('box_files.fileTypeId,box_files.boxId,box_files.extension, box_files.fileSize')
            ->join('box_files box_files', 'box_files.id = elements.id');

        if ($criteria->fileTypeId) {
            $query->andWhere(DbHelper::parseParam('box_files.fileTypeId', $criteria->fileTypeId, $query->params));
        }

        if ($criteria->fileType) {
            $query->join('box_fileType box_fileType', 'box_fileType.id = box_files.fileTypeId');
            $query->andWhere(DbHelper::parseParam('box_fileType.handle', $criteria->fileType, $query->params));
        }

        if ($criteria->order) {
            $query->order($criteria->order);
        }
    }

    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     *
     * @return array
     */
    public function populateElementModel($row)
    {
        return Box_FileModel::populateModel($row);
    }

    /**
     * Returns the HTML for an editor HUD for the given element.
     *
     * @param BaseElementModel $element
     *
     * @return string
     */
    public function getEditorHtml(BaseElementModel $element)
    {
        $html = craft()->templates->render('box/_editor', array(
            'element' => $element,
            'hud' => true,
        ));

        return $html;
    }
}
