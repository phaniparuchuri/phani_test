<?php

namespace Craft;

/**
 * Box FileType Model.
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
 *
 * @method FieldLayoutModel getFieldLayout()
 * @method setFieldLayout(FieldLayoutModel $fieldLayout)
 */
class Box_FileTypeModel extends BaseModel
{
    /**
     * Use the translated fileType name as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return Craft::t($this->name);
    }

    /**
     * Define the model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'name' => AttributeType::String,
            'handle' => AttributeType::String,
            'fieldLayoutId' => AttributeType::Number,
        );
    }

    /**
     * Inject this model's behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return array(
            'fieldLayout' => new FieldLayoutBehavior('Box_File'),
        );
    }
}
