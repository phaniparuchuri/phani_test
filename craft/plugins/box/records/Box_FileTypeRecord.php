<?php

namespace Craft;

/**
 * Box FileType Record.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property int $fieldLayoutId
 */
class Box_FileTypeRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'box_filetype';
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'name' => array(AttributeType::Name, 'required' => true),
            'handle' => array(AttributeType::Handle, 'required' => true),
            'fieldLayoutId' => AttributeType::Number,
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'fieldLayout' => array(static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::SET_NULL),
            'boxFiles' => array(static::HAS_MANY, 'Box_FileRecord', 'boxFileId'),
        );
    }

    /**
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('name'), 'unique' => true),
            array('columns' => array('handle'), 'unique' => true),
        );
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'ordered' => array('order' => 'name'),
        );
    }
}
