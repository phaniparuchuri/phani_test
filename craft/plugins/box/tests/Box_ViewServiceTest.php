<?php

namespace Craft;

/**
 * Box View Service Test.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @coversDefaultClass Craft\Box_ViewService
 * @covers ::<!public>
 */
class Box_ViewServiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Set up parent
        parent::setUpBeforeClass();

        // Require dependencies
        require_once __DIR__.'/../services/Box_ViewService.php';
        require_once __DIR__.'/../services/Box_ContentService.php';
        require_once __DIR__.'/../vendor/autoload.php';

        // Mock authorization code
        $_GET['code'] = 'test';
    }

    /**
     * Test create view client.
     *
     * @covers ::createViewClient
     */
    final public function testCreateViewClient()
    {
        $service = new Box_ViewService();
        $client = $service->createViewClient();

        $this->assertInstanceOf('\AdammBalogh\Box\ViewClient', $client);
    }

    /**
     * Test create session.
     *
     * @expectedException Craft\HttpException
     *
     * @covers ::createSession
     */
    final public function testCreateSession()
    {
        $this->setComponent(craft(), 'box_content', new Box_ContentService());

        $service = new Box_ViewService();
        $service->createSession(1);
    }

    /**
     * Test list documents.
     *
     * @expectedException Craft\HttpException
     *
     * @covers ::listDocuments
     */
    final public function testListDocuments()
    {
        $service = new Box_ViewService();
        $service->listDocuments();
    }

    /**
     * Test get document content.
     *
     * @expectedException Craft\HttpException
     *
     * @covers ::getDocumentContent
     */
    final public function testGetDocumentContent()
    {
        $service = new Box_ViewService();
        $service->getDocumentContent(1);
    }

    /**
     * Test get thumbnail.
     *
     * @covers ::getThumbnail
     */
    final public function testGetThumbnail()
    {
        $service = new Box_ViewService();
        $result = $service->getThumbnail(1);

        $this->assertFalse($result);
    }

    /**
     * Test delete document.
     *
     * @expectedException Craft\HttpException
     *
     * @covers ::deleteDocument
     */
    final public function testDeleteDocument()
    {
        $service = new Box_ViewService();
        $service->deleteDocument(1);
    }
}
