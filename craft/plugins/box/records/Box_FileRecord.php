<?php

namespace Craft;

/**
 * Box File Record.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @property int $id
 * @property int $elementId
 * @property int $fileTypeId
 * @property string $boxId
 * @property int $fileSize
 * @property string $extension
 */
class Box_FileRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'box_files';
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'boxId' => array('column' => ColumnType::Varchar, 'maxLength' => 50, 'required' => true),
            'fileSize' => array('column' => ColumnType::Int, 'maxLength' => 11),
            'extension' => array('column' => ColumnType::Varchar, 'maxLength' => 4, 'required' => true),
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
            'fileType' => array(static::BELONGS_TO, 'Box_FileTypeRecord', 'required' => true, 'onDelete' => static::CASCADE),
        );
    }
}
