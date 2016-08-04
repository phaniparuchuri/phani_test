<?php

namespace Craft;

/**
 * Box Controller.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class BoxController extends BaseController
{
    /**
     * Download a document.
     */
    public function actionDownloadDoc()
    {
        $docID = craft()->request->getParam('id');
        $downloadURL = craft()->box_content->getTempDownloadUrl($docID);
        $this->redirect($downloadURL);
    }

    /**
     * Upload a file.
     */
    public function actionUploadFile()
    {
        // require Ajax
        $this->requireAjaxRequest();

        // fetch folderId
        $fileTypeId = craft()->request->getPost('folderId');

        // actually upload the file
        $response = craft()->box->uploadFile($fileTypeId);

        $this->returnJson($response->getResponseData());
    }

    /**
     * Render the box files element index.
     *
     * @param array $variables
     */
    public function actionBoxFileIndex(array $variables = array())
    {
        $variables['file_types'] = craft()->box_fileType->getAllfileTypes();

        $this->renderTemplate('box/_index', $variables);
    }

    /**
     * Edit a file.
     *
     * @param array $variables
     *
     * @throws HttpException
     */
    public function actionEditFile(array $variables = array())
    {
        if (!empty($variables['fileTypeHandle'])) {
            $variables['fileType'] = craft()->box_fileType->getfileTypeByHandle($variables['fileTypeHandle']);
        } elseif (!empty($variables['fileTypeId'])) {
            $variables['fileType'] = craft()->box_fileType->getfileTypeById($variables['fileTypeId']);
        }

        if (empty($variables['fileType'])) {
            throw new HttpException(404);
        }

        // Now let's set up the actual boxFile
        if (empty($variables['element'])) {
            if (!empty($variables['boxFileId'])) {
                $variables['element'] = craft()->box->getboxFileById($variables['boxFileId']);

                if (!$variables['element']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['element'] = new Box_FileModel();
                $variables['element']->fileTypeId = $variables['fileType']->id;
            }
        }

        // Tabs
        $variables['tabs'] = array();

        foreach ($variables['fileType']->getFieldLayout()->getTabs() as $index => $tab) {
            // Do any of the fields on this tab have errors?
            $hasErrors = false;

            if ($variables['element']->hasErrors()) {
                foreach ($tab->getFields() as $field) {
                    if ($variables['element']->getErrors($field->getField()->handle)) {
                        $hasErrors = true;
                        break;
                    }
                }
            }

            $variables['tabs'][] = array(
                'label' => $tab->name,
                'url' => '#tab'.($index + 1),
                'class' => ($hasErrors ? 'error' : null),
            );
        }

        if (!$variables['element']->id) {
            $variables['title'] = Craft::t('Add a new file');
        } else {
            $variables['title'] = $variables['element']->title;
        }

        // Breadcrumbs
        $variables['crumbs'] = array(
            array('label' => Craft::t('Files'), 'url' => UrlHelper::getUrl('box')),
            array('label' => $variables['fileType']->name, 'url' => UrlHelper::getUrl('files')),
        );

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'box/'.$variables['fileType']->handle.'/{id}';

        // Render the template!
        $this->renderTemplate('box/_edit', $variables);
    }

    /**
     * Saves a boxFile.
     *
     * @throws Exception
     */
    public function actionSaveboxFile()
    {
        $this->requirePostRequest();

        $boxFileId = craft()->request->getPost('boxFileId');

        if ($boxFileId) {
            $boxFile = craft()->box->getboxFileById($boxFileId);

            if (!$boxFile) {
                throw new Exception(Craft::t('No boxFile exists with the ID “{id}”', array('id' => $boxFileId)));
            }
        } else {
            $boxFile = new Box_FileModel();
        }

        // Set the boxFile attributes
        $boxFile->fileTypeId = craft()->request->getPost('fileTypeId', $boxFile->fileTypeId);
        $boxFile->getContent()->title = craft()->request->getPost('title', $boxFile->title);
        $boxFile->setContentFromPost('fields');

        // check for new document version
        $uploadedFile = UploadedFile::getInstancesByName('new_version');
        $new_version = array_shift($uploadedFile);

        if ($new_version) {
            $tempPath = AssetsHelper::getTempFilePath($new_version->getName());
            move_uploaded_file($new_version->getTempName(), $tempPath);
            craft()->box_content->uploadNewVersion($boxFile->boxId, $tempPath);
        }

        if (craft()->box->saveboxFile($boxFile)) {
            craft()->userSession->setNotice(Craft::t('boxFile saved.'));
            $this->redirectToPostedUrl($boxFile);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save boxFile.'));

            // Send the boxFile back to the template
            craft()->urlManager->setRouteVariables(array(
                'boxFile' => $boxFile,
            ));
        }
    }

    /**
     * Deletes a boxFile.
     */
    public function actionDeleteFile()
    {
        $this->requirePostRequest();

        $boxFileId = craft()->request->getRequiredPost('fileId');

        if (craft()->elements->deleteElementById($boxFileId)) {
            craft()->userSession->setNotice(Craft::t('File deleted.'));
            $this->redirectToPostedUrl();
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t delete file.'));
        }
    }

    /**
     * Saves Box acquired token.
     */
    public function actionSaveToken()
    {
        // require code querystring param
        craft()->request->getRequiredParam('code');

        // save token
        craft()->box_content->saveToken();

        // end request here
        craft()->end();
    }

    /**
     * Generates a temp download url for an older version of this file.
     *
     * @param int $fileId
     */
    public function actionDownloadOlderVerion($fileId, $versionId)
    {
        $temp_download_url = craft()->box_content->getTempDownloadUrlOlderVersion($fileId, $versionId);
        $this->redirect($temp_download_url, true, 302);
    }

    /**
     * Generates temp download url.
     *
     * @throws HttpException
     */
    public function actionDownloadFile($docId, $fileId)
    {
        // fetch document entry
        $document = craft()->entries->getEntryById($docId);

        // check for approval or direct access
        if (!craft()->technicalCloud->canViewDocument($document) || !$document->documentOptions->contains('download')) {
            throw new HttpException(403);
        }
        craft()->box->getboxFileById($docId);

        $temp_download_url = craft()->box_content->getTempDownloadUrl($fileId);
        $this->redirect($temp_download_url, true, 302);
    }
}
