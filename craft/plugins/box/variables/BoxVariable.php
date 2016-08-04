<?php

namespace Craft;

/**
 * Box Variable.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class BoxVariable
{
    /**
     * Get box file elements.
     *
     * @return ElementCriteriaModel
     */
    public function boxFiles()
    {
        return craft()->elements->getCriteria('Box_File');
    }

    /**
     * Create a viewing session by document id.
     *
     * @param int $docId
     *
     * @return SuccessResponse|null
     */
    public function createSession($docId)
    {
        return craft()->box_view->createSession($docId);
    }

    /**
     * Get Box file info.
     *
     * @param int $fileId
     *
     * @return array|null
     */
    public function fileInfo($fileId)
    {
        return craft()->box_content->getDocumentInfo($fileId);
    }

    /**
     * Get Box file versions.
     *
     * @param int $fileId
     *
     * @return array|null
     */
    public function fileVersions($fileId)
    {
        return craft()->box_content->getFileVersion($fileId);
    }

    /**
     * Get Box file element.
     *
     * @param int $elementId
     *
     * @return Box_FileModel|null
     */
    public function getElementById($elementId)
    {
        return craft()->box->getboxFileById($elementId);
    }

    /**
     * Get Box file temp download url.
     *
     * @param int $docId
     *
     * @return string|null
     */
    public function getTempDownloadUrl($docId)
    {
        return craft()->box_content->getTempDownloadUrl($docId);
    }
}
