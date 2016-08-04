<?php

namespace Craft;

/**
 * Box File Type Service.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_FileTypeService extends BaseApplicationComponent
{
    /**
     * @var array
     */
    private $_allfileTypeIds;

    /**
     * @var array
     */
    private $_fileTypesById;

    /**
     * @var bool
     */
    private $_fetchedAllfileTypes = false;

    /**
     * Returns all of the fileType IDs.
     *
     * @return array
     */
    public function getAllfileTypeIds()
    {
        if (!isset($this->_allfileTypeIds)) {
            if ($this->_fetchedAllfileTypes) {
                $this->_allfileTypeIds = array_keys($this->_fileTypesById);
            } else {
                $this->_allfileTypeIds = craft()->db->createCommand()
                    ->select('id')
                    ->from('box_fileType')
                    ->queryColumn();
            }
        }

        return $this->_allfileTypeIds;
    }

    /**
     * Returns all fileTypes.
     *
     * @param string|null $indexBy
     *
     * @return array
     */
    public function getAllfileTypes($indexBy = null)
    {
        if (!$this->_fetchedAllfileTypes) {
            $fileTypeRecords = Box_FileTypeRecord::model()->ordered()->findAll();
            $this->_fileTypesById = Box_FileTypeModel::populateModels($fileTypeRecords, 'id');
            $this->_fetchedAllfileTypes = true;
        }

        if ($indexBy == 'id') {
            return $this->_fileTypesById;
        } elseif ($indexBy === null) {
            return array_values($this->_fileTypesById);
        } else {
            $fileTypes = array();

            foreach ($this->_fileTypesById as $fileType) {
                $fileTypes[$fileType->$indexBy] = $fileType;
            }

            return $fileTypes;
        }
    }

    /**
     * Gets the total number of fileTypes.
     *
     * @return int
     */
    public function getTotalfileTypes()
    {
        return count($this->getAllfileTypeIds());
    }

    /**
     * Returns a fileType by its ID.
     *
     * @param int $fileTypeId
     *
     * @return Box_FileTypeModel|null
     */
    public function getfileTypeById($fileTypeId)
    {
        if (!isset($this->_fileTypesById) || !array_key_exists($fileTypeId, $this->_fileTypesById)) {
            $fileTypeRecord = Box_FileTypeRecord::model()->findById($fileTypeId);

            if ($fileTypeRecord) {
                $this->_fileTypesById[$fileTypeId] = Box_FileTypeModel::populateModel($fileTypeRecord);
            } else {
                $this->_fileTypesById[$fileTypeId] = null;
            }
        }

        return $this->_fileTypesById[$fileTypeId];
    }

    /**
     * Gets a fileType by its handle.
     *
     * @param string $fileTypeHandle
     *
     * @return Box_FileTypeModel|null
     */
    public function getfileTypeByHandle($fileTypeHandle)
    {
        $fileTypeRecord = Box_FileTypeRecord::model()->findByAttributes(array(
            'handle' => $fileTypeHandle,
        ));

        if ($fileTypeRecord) {
            return Box_FileTypeModel::populateModel($fileTypeRecord);
        }
    }

    /**
     * Saves a fileType.
     *
     * @param Box_FileTypeModel $fileType
     *
     * @throws Exception
     *
     * @return bool
     */
    public function savefileType(Box_FileTypeModel $fileType)
    {
        if ($fileType->id) {
            $fileTypeRecord = Box_FileTypeRecord::model()->findById($fileType->id);

            if (!$fileTypeRecord) {
                throw new Exception(Craft::t('No fileType exists with the ID “{id}”', array('id' => $fileType->id)));
            }

            $oldfileType = Box_FileTypeModel::populateModel($fileTypeRecord);
            $isNewfileType = false;
        } else {
            $fileTypeRecord = new Box_FileTypeRecord();
            $isNewfileType = true;
            $oldfileType = $fileTypeRecord;
        }

        $fileTypeRecord->name = $fileType->name;
        $fileTypeRecord->handle = $fileType->handle;

        $fileTypeRecord->validate();
        $fileType->addErrors($fileTypeRecord->getErrors());

        if (!$fileType->hasErrors()) {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try {
                if (!$isNewfileType && $oldfileType->fieldLayoutId) {
                    // Drop the old field layout
                    craft()->fields->deleteLayoutById($oldfileType->fieldLayoutId);
                }

                // Save the new one
                $fieldLayout = $fileType->getFieldLayout();
                craft()->fields->saveLayout($fieldLayout);

                // Update the fileType record/model with the new layout ID
                $fileType->fieldLayoutId = $fieldLayout->id;
                $fileTypeRecord->fieldLayoutId = $fieldLayout->id;

                // Save it!
                $fileTypeRecord->save(false);

                // Now that we have a fileType ID, save it on the model
                if (!$fileType->id) {
                    $fileType->id = $fileTypeRecord->id;
                }

                // Might as well update our cache of the fileType while we have it.
                $this->_fileTypesById[$fileType->id] = $fileType;

                if ($transaction !== null) {
                    $transaction->commit();
                }
            } catch (\Exception $e) {
                if ($transaction !== null) {
                    $transaction->rollback();
                }

                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * Deletes a fileType by its ID.
     *
     * @param int $fileTypeId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deletefileTypeById($fileTypeId)
    {
        if (!$fileTypeId) {
            return false;
        }

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try {
            // Delete the field layout
            $fieldLayoutId = craft()->db->createCommand()
                ->select('fieldLayoutId')
                ->from('box_fileType')
                ->where(array('id' => $fileTypeId))
                ->queryScalar();

            if ($fieldLayoutId) {
                craft()->fields->deleteLayoutById($fieldLayoutId);
            }

            // Grab the boxFile ids so we can clean the elements table.
            $boxFileIds = craft()->db->createCommand()
                ->select('id')
                ->from('boxFiles')
                ->where(array('fileTypeId' => $fileTypeId))
                ->queryColumn();

            craft()->elements->deleteElementById($boxFileIds);

            $affectedRows = craft()->db->createCommand()->delete('box_fileType', array('id' => $fileTypeId));

            if ($transaction !== null) {
                $transaction->commit();
            }

            return (bool) $affectedRows;
        } catch (\Exception $e) {
            if ($transaction !== null) {
                $transaction->rollback();
            }

            throw $e;
        }
    }
}
