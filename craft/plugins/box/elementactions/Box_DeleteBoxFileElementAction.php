<?php

namespace Craft;

/**
 * Box DeleteBoxFileElementAction.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class Box_DeleteBoxFileElementAction extends BaseElementAction
{
    /**
     * Get elementaction name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Delete file');
    }

    /**
     * Has a destructive action?
     *
     * @return bool
     */
    public function isDestructive()
    {
        return true;
    }

    /**
     * Perform the delete action.
     *
     * @param ElementCriteriaModel $criteria
     *
     * @return bool
     */
    public function performAction(ElementCriteriaModel $criteria)
    {
        // Fetch element
        $ids = $criteria->ids();
        $elementId = array_shift($ids);
        $element = craft()->box->getboxFileById($elementId);

        // Delete file in Box view
        craft()->box_content->deleteDocument($element->boxId);

        // Success!
        $this->setMessage(Craft::t('Box file removed successfully.'));

        // Delete element itself
        craft()->elements->deleteElementById($elementId);

        return true;
    }
}
