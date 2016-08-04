<?php

namespace Craft;

/**
 * Box Content Service Test.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @coversDefaultClass Craft\Box_ContentService
 * @covers ::<!public>
 */
class Box_ContentServiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Set up parent
        parent::setUpBeforeClass();

        // Require dependencies
        require_once __DIR__.'/../services/Box_ContentService.php';
        require_once __DIR__.'/../vendor/autoload.php';

        // Mock authorization code
        $_GET['code'] = 'test';
    }

    /**
     * Test create client.
     *
     * @covers ::createClient
     */
    final public function testCreateClient()
    {
        $service = new Box_ContentService();
        $client = $service->createClient();

        $this->assertInstanceOf('\AdammBalogh\Box\ContentClient', $client);
    }

    /**
     * Test get temp download url older version.
     *
     * @covers ::getTempDownloadUrlOlderVersion
     */
    final public function testGetTempDownloadUrlOlderVersion()
    {
        $service = new Box_ContentService();
        $result = $service->getTempDownloadUrlOlderVersion(1, 1);

        $this->assertNull($result);
    }

    /**
     * Test get temp download url.
     *
     * @covers ::getTempDownloadUrl
     */
    final public function testGetTempDownloadUrl()
    {
        $service = new Box_ContentService();
        $result = $service->getTempDownloadUrl(1);

        $this->assertNull($result);
    }

    /**
     * Test get file thumbnail.
     *
     * @covers ::GetFileThumbnail
     */
    final public function testGetFileThumbnail()
    {
        $service = new Box_ContentService();
        $result = $service->GetFileThumbnail(1);

        $this->assertNull($result);
    }

    /**
     * Test get document info.
     *
     * @covers ::getDocumentInfo
     */
    final public function testGetDocumentInfo()
    {
        $service = new Box_ContentService();
        $result = $service->getDocumentInfo(1);

        $this->assertNull($result);
    }

    /**
     * Test get file version.
     *
     * @covers ::getFileVersion
     */
    final public function testGetFileVersion()
    {
        $service = new Box_ContentService();
        $result = $service->getFileVersion(1);

        $this->assertNull($result);
    }

    /**
     * Test delete document.
     *
     * @covers ::deleteDocument
     */
    final public function testDeleteDocument()
    {
        $service = new Box_ContentService();
        $result = $service->deleteDocument(1);

        $this->assertFalse($result);
    }

    /**
     * Test upload new version.
     *
     * @covers ::uploadNewVersion
     */
    final public function testUploadNewVersion()
    {
        $service = new Box_ContentService();
        $result = $service->uploadNewVersion(1, __FILE__);

        $this->assertFalse($result);
    }

    /**
     * Test save token.
     *
     * @covers ::saveToken
     */
    final public function testSaveToken()
    {
        $service = new Box_ContentService();
        $result = $service->saveToken();

        $this->assertNull($result);
    }
}
