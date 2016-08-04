<?php

namespace Craft;

/**
 * Box File Model.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @property int $id
 * @property int $fileTypeId
 * @property string $boxId
 * @property int $fileSize
 * @property string $extension
 * @property string $title
 */
class Box_FileModel extends BaseElementModel
{
    /**
     * Status constants.
     */
    const READY = 'live';
    const PROCESSING = 'pending';

    /**
     * Element Type.
     *
     * @var string
     */
    protected $elementType = 'Box_File';

    /**
     * Define this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'fileTypeId' => AttributeType::Number,
            'boxId' => AttributeType::String,
            'fileSize' => AttributeType::Number,
            'extension' => AttributeType::String,
        ));
    }

    /**
     * Use the file's title as its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getContent()->title;
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns whether the file is editable (files like doc,xls etc.).
     *
     * @return bool
     */
    public function isEditableFile()
    {
        return in_array($this->extension, array('doc', 'docx', 'xls', 'xslx', 'ppt', 'pptx'));
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        $fileType = $this->getType();

        if ($fileType) {
            return UrlHelper::getCpUrl('box/'.$fileType->handle.'/'.$this->id);
        }
    }

    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayoutModel|null
     */
    public function getFieldLayout()
    {
        $fileType = $this->getType();

        if ($fileType) {
            return $fileType->getFieldLayout();
        }
    }

    /**
     * Returns the boxFile's type.
     *
     * @return Box_FileTypeModel|null
     */
    public function getType()
    {
        if ($this->fileTypeId) {
            return craft()->box_fileType->getfileTypeById($this->fileTypeId);
        }
    }

    /**
     * Returns the boxFile's type.
     *
     * @return Box_FileTypeModel|null
     */
    public function getFileType()
    {
        return $this->getType();
    }
}
