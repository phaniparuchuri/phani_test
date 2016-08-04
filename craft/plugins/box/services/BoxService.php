<?php

namespace Craft;

use AdammBalogh\Box\Command\Content;
use AdammBalogh\Box\Factory\ResponseFactory;
use AdammBalogh\Box\GuzzleHttp\Message\SuccessResponse;
use AdammBalogh\Box\GuzzleHttp\Message\ErrorResponse;

/**
 * Box Service.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class BoxService extends BaseApplicationComponent
{
    /**
     * Returns an boxFile by its ID.
     *
     * @param int $boxFileId
     *
     * @return Box_FileModel|null
     */
    public function getboxFileById($boxFileId)
    {
        return craft()->elements->getElementById($boxFileId, 'Box_File');
    }

    /**
     * Saves a boxFile.
     *
     * @param Box_FileModel $boxFile
     *
     * @throws Exception
     *
     * @return bool
     */
    public function saveboxFile(Box_FileModel $boxFile)
    {
        $isNewboxFile = !($boxFile->id);

        // boxFile data
        if (!$isNewboxFile) {
            $boxFileRecord = Box_FileRecord::model()->findById($boxFile->id);

            if (!$boxFileRecord) {
                throw new Exception(Craft::t('No boxFile exists with the ID “{id}”', array('id' => $boxFile->id)));
            }
        } else {
            $boxFileRecord = new Box_FileRecord();
            $boxFileRecord->extension = IOHelper::getExtension($boxFile->title);
        }

        $boxFileRecord->fileTypeId = $boxFile->fileTypeId;
        $boxFileRecord->boxId = $boxFile->boxId;
        $boxFileRecord->fileSize = $boxFile->fileSize;

        $boxFileRecord->validate();
        $boxFile->addErrors($boxFileRecord->getErrors());

        if (!$boxFile->hasErrors()) {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try {
                // Fire an 'onBeforeSaveBoxFile' event
                $this->onBeforeSaveBoxFile(new Event($this, array(
                    'boxFile' => $boxFile,
                    'isNewboxFile' => $isNewboxFile,
                )));

                if (craft()->elements->saveElement($boxFile)) {
                    // Now that we have an element ID, save it on the other stuff
                    if ($isNewboxFile) {
                        $boxFileRecord->id = $boxFile->id;
                    }

                    $boxFileRecord->save(false);

                    // Fire an 'onSaveBoxFile' event
                    $this->onSaveBoxFile(new Event($this, array(
                        'boxFile' => $boxFile,
                        'isNewboxFile' => $isNewboxFile,
                    )));

                    if ($transaction !== null) {
                        $transaction->commit();
                    }

                    return true;
                }
            } catch (\Exception $e) {
                if ($transaction !== null) {
                    $transaction->rollback();
                }

                throw $e;
            }
        }

        return false;
    }

    /**
     * Upload a file.
     *
     * @param int $fileTypeId The Id of the file's filetype
     *
     * @return Box_OperationResponseModel
     */
    public function uploadFile($fileTypeId)
    {
        try {
            $fileType = craft()->box_fileType->getfileTypeById($fileTypeId);

            // use first filetype if none was found
            if ($fileType == null) {
                $fileTypes = craft()->box_fileType->getAllfileTypes();
                $fileType = array_shift($fileTypes);
            }

            return $this->handleFileUpload($fileType);
        } catch (Exception $exception) {
            $response = new Box_OperationResponseModel();
            $response->setError(Craft::t('Error uploading the file: {error}', array('error' => $exception->getMessage())));

            return $response;
        }
    }

    /**
     * Handle file upload.
     *
     * @param Box_FileTypeModel $fileType
     *
     * @throws Exception
     *
     * @return Box_OperationResponseModel
     */
    private function handleFileUpload(Box_FileTypeModel $fileType)
    {
        // Upload the file and drop it in the temporary folder
        $file = UploadedFile::getInstanceByName('box-upload');

        // Make sure a file was uploaded
        if (empty($file->name)) {
            throw new Exception(Craft::t('No file was uploaded'));
        }

        // Make sure the file isn't empty
        if (!$file->size) {
            throw new Exception(Craft::t('Uploaded file was empty'));
        }

        $response = $this->uploadToBox($file, $fileType);

        return $response;
    }

    /**
     * Upload a file to Box.
     *
     * @param \CUploadedFile    $file
     * @param Box_FileTypeModel $fileType
     *
     * @throws Exception
     *
     * @return Box_OperationResponseModel
     */
    private function uploadToBox(\CUploadedFile $file, Box_FileTypeModel $fileType)
    {
        $fileName = AssetsHelper::cleanAssetName($file->name);

        // Create Box Content client
        $contentClient = craft()->box_content->createClient();

        // Save the file to a temp location and pass this on to the source type implementation
        $filePath = AssetsHelper::getTempFilePath(IOHelper::getExtension($fileName));
        move_uploaded_file($file->tempName, $filePath);

        // execute multipart document upload command
        $parent_folder_id = craft()->config->get('boxContentFolderId');
        $command = new Content\File\UploadFile($fileName, $parent_folder_id, file_get_contents($filePath));
        $http_response = ResponseFactory::getResponse($contentClient, $command);

        // Decode Box response
        $response_object = $http_response->json();

        // create response model
        $action_response = new Box_OperationResponseModel();
        $action_response->setDataItem('response', $response_object);

        // upload to Box was successful and authenticated
        if ($http_response instanceof SuccessResponse && $http_response->getStatusCode() != 401) {

            // Fetch first uploaded file entry
            $file_entry = array_shift($response_object['entries']);

            // Create new file Craft element
            $boxFile = new Box_FileModel();
            $boxFile->fileTypeId = $fileType->id;
            $boxFile->fileSize = $file->size;
            $boxFile->boxId = $file_entry['id'];
            $boxFile->getContent()->title = $fileName;

            if ($this->saveboxFile($boxFile)) {
                $action_response->setSuccess();
                $action_response->setDataItem('fileId', $boxFile->id);
            } else {
                $action_response->setError(Craft::t('Box file element save error.'));
            }
        }

        // upload to Box was unsuccessful
        elseif ($http_response instanceof ErrorResponse) {
            $action_response->setError($response_object['message']);
        }

        return $action_response;
    }

    // Events

    /**
     * Fires an 'onBeforeSaveBoxFIle' event.
     *
     * @param Event $event
     *
     * @codeCoverageIgnore
     */
    public function onBeforeSaveBoxFile(Event $event)
    {
        $this->raiseEvent('onBeforeSaveBoxFile', $event);
    }

    /**
     * Fires an 'onSaveBoxFile' event.
     *
     * @param Event $event
     *
     * @codeCoverageIgnore
     */
    public function onSaveBoxFile(Event $event)
    {
        $this->raiseEvent('onSaveBoxFile', $event);
    }
}
