<?php

namespace Craft;

/**
 * Box FileType Controller.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_FileTypeController extends BaseController
{
    /**
     * Show FileType element index.
     *
     * @param array $variables
     */
    public function actionfileTypeIndex(array $variables = array())
    {
        $variables['file_types'] = craft()->box_fileType->getAllfileTypes();

        $this->renderTemplate('box/fileTypes', $variables);
    }

    /**
     * Edit a fileType.
     *
     * @param array $variables
     *
     * @throws HttpException
     * @throws Exception
     */
    public function actionEditfileType(array $variables = array())
    {
        $variables['brandNewfileType'] = false;

        if (!empty($variables['fileTypeId'])) {
            if (empty($variables['fileType'])) {
                $variables['fileType'] = craft()->box_fileType->getfileTypeById($variables['fileTypeId']);

                if (!$variables['fileType']) {
                    throw new HttpException(404);
                }
            }

            $variables['title'] = $variables['fileType']->name;
        } else {
            if (empty($variables['fileType'])) {
                $variables['fileType'] = new Box_FileTypeModel();
                $variables['brandNewfileType'] = true;
            }

            $variables['title'] = Craft::t('Create a new filetype');
        }

        $variables['crumbs'] = array(
            array('label' => Craft::t('Files'), 'url' => UrlHelper::getUrl('box')),
            array('label' => Craft::t('File types'), 'url' => UrlHelper::getUrl('box/filetypes')),
        );

        $this->renderTemplate('box/fileTypes/_edit', $variables);
    }

    /**
     * Saves a fileType.
     */
    public function actionSavefileType()
    {
        $this->requirePostRequest();

        $fileType = new Box_FileTypeModel();

        // Shared attributes
        $fileType->id = craft()->request->getPost('fileTypeId');
        $fileType->name = craft()->request->getPost('name');
        $fileType->handle = craft()->request->getPost('handle');

        // Set the field layout
        $fieldLayout = craft()->fields->assembleLayoutFromPost();
        $fieldLayout->type = ElementType::Asset;
        $fileType->setFieldLayout($fieldLayout);

        // Save it
        if (craft()->box_fileType->savefileType($fileType)) {
            craft()->userSession->setNotice(Craft::t('fileType saved.'));
            $this->redirectToPostedUrl($fileType);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save fileType.'));
        }

        // Send the fileType back to the template
        craft()->urlManager->setRouteVariables(array(
            'fileType' => $fileType,
        ));
    }

    /**
     * Deletes a fileType.
     */
    public function actionDeletefileType()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $fileTypeId = craft()->request->getRequiredPost('id');

        craft()->box_fileType->deletefileTypeById($fileTypeId);
        $this->returnJson(array('success' => true));
    }
}
