<?php

namespace Craft;

/**
 * Box Plugin.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 */
class BoxPlugin extends BasePlugin
{
    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Box');
    }

    /**
     * Get plugin description.
     *
     * @return string
     */
    public function getDescription()
    {
        return Craft::t('Integrates Box with Craft Assets');
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * Get plugin developer.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Nerds & Company';
    }

    /**
     * Get plugin developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'https://nerds.company';
    }

    /**
     * Has CP section.
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * Register CP routes.
     *
     * @return array
     */
    public function registerCpRoutes()
    {
        return array(
            'box/filetypes' => array('action' => 'box_FileType/fileTypeIndex'),
            'box/filetypes/new' => array('action' => 'box_FileType/editfileType'),
            'box/filetypes/(?P<fileTypeId>\d+)' => array('action' => 'box_FileType/editfileType'),
            'box' => array('action' => 'box/boxFileIndex'),
            'box/(?P<fileTypeHandle>{handle})/new' => array('action' => 'box/editFile'),
            'box/(?P<fileTypeHandle>{handle})/(?P<boxFileId>\d+)' => array('action' => 'box/editFile'),
            'box/viewer/document-(?P<boxId>(.*?))' => 'box/viewer',
        );
    }

    /**
     * Initialize plugin.
     */
    public function init()
    {
        if (craft()->userSession->isAdmin()) {

            // check whether api key setting is present in config
            if (craft()->config->get('boxViewApiKey') == null) {
                craft()->userSession->setError(Craft::t('boxViewApiKey config setting missing.'));
            }

            // check whether api client id is present in config
            if (craft()->config->get('boxContentClientId') == null) {
                craft()->userSession->setError(Craft::t('boxContentClientId config setting missing.'));
            }

            // check whether api client secret is present in config
            if (craft()->config->get('boxContentClientSecret') == null) {
                craft()->userSession->setError(Craft::t('boxContentClientSecret config setting missing.'));
            }

            // check whether api redirect uri is present in config
            if (craft()->config->get('boxContentRedirectUri') == null) {
                craft()->userSession->setError(Craft::t('boxContentRedirectUri config setting missing.'));
            }
        }

        include CRAFT_PLUGINS_PATH.'/box/vendor/autoload.php';
    }

    /**
     * Get settings url.
     *
     * @return string
     */
    public function getSettingsUrl()
    {
        return 'box/filetypes';
    }
}
