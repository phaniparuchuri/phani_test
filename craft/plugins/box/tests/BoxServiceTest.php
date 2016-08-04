<?php

namespace Craft;

/**
 * Box Service Test.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @coversDefaultClass Craft\BoxService
 * @covers ::<!public>
 */
class BoxServiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Set up parent
        parent::setUpBeforeClass();

        // Require dependencies
        require_once __DIR__.'/../services/BoxService.php';
        require_once __DIR__.'/../models/Box_FileModel.php';
        require_once __DIR__.'/../records/Box_FileRecord.php';
        require_once __DIR__.'/../services/Box_FileTypeService.php';
        require_once __DIR__.'/../models/Box_FileTypeModel.php';
        require_once __DIR__.'/../models/Box_OperationResponseModel.php';
        require_once __DIR__.'/../services/Box_ContentService.php';
        require_once __DIR__.'/../vendor/autoload.php';
    }

    /**
     * Test get box file by id.
     *
     * @covers ::getboxFileById
     */
    final public function testGetboxFileById()
    {
        $this->setMockElementsService();

        $service = new BoxService();
        $result = $service->getboxFileById(1);

        $this->assertTrue($result);
    }

    /**
     * Test save box file.
     *
     * @param bool $withId
     * @param bool $withReturn
     *
     * @covers ::saveboxFile
     * @dataProvider provideSaveBoxFilePaths
     */
    final public function testSaveboxFile($withId, $withReturn)
    {
        $this->setMockElementsService();

        $boxFile = $this->getMockBoxFileModel($withId, $withReturn);

        $service = new BoxService();

        try {
            $result = $service->saveboxFile($boxFile);
            $this->assertFalse($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e);
        }
    }

    /**
     * Test upload file.
     *
     * @param string $name
     * @param int    $size
     *
     * @covers ::uploadFile
     * @dataProvider provideUploadFilePaths
     */
    final public function testUploadFile($name, $size)
    {
        $this->setMockBoxFileTypeService();
        $this->setMockUploadedFile($name, $size);
        $this->setMockBoxContentService();

        $service = new BoxService();
        $response = $service->uploadFile(1);

        $this->assertInstanceOf('Craft\Box_OperationResponseModel', $response);
    }

    /**
     * Provide save box file paths.
     *
     * @return array
     */
    final public function provideSaveBoxFilePaths()
    {
        return array(
            'With exception' => array(false, false),
            'With id' => array(true, false),
            'With return' => array(false, true),
        );
    }

    /**
     * Provide upload file paths.
     *
     * @return array
     */
    final public function provideUploadFilePaths()
    {
        return array(
            'With name' => array('test.pdf', 1),
            'Without name' => array('', 1),
            'Without size' => array('test.pdf', 0),
        );
    }

    /**
     * Mock ElementsService.
     */
    private function setMockElementsService()
    {
        $mock = $this->getMockBuilder('Craft\ElementsService')
            ->disableOriginalConstructor()
            ->setMethods(array('getElementById', 'saveElement'))
            ->getMock();

        $mock->expects($this->any())->method('getElementById')->willReturn(true);
        $mock->expects($this->any())->method('saveElement')->will($this->throwException(new \Exception()));

        $this->setComponent(craft(), 'elements', $mock);
    }

    /**
     * Mock Box_FileModel.
     *
     * @param bool $withId
     * @param bool $withReturn
     *
     * @return Box_FileModel
     */
    private function getMockBoxFileModel($withId = false, $withReturn = false)
    {
        $mock = $this->getMockBuilder('Craft\Box_FileModel')
            ->disableOriginalConstructor()
            ->setMethods(array('__get'))
            ->getMock();

        $mock->expects($this->any())->method('__get')->will($this->returnCallback(function ($key) use ($withId, $withReturn) {
            switch ($key) {
                case 'id':
                    return $withId;

                case 'title':
                    return 'test.pdf';

                case 'boxId':
                    return $withReturn ? null : 1;
            }
        }));

        return $mock;
    }

    /**
     * Mock Box_FileTypeService.
     */
    private function setMockBoxFileTypeService()
    {
        $mock = $this->getMockBuilder('Craft\Box_FileTypeService')
            ->disableOriginalConstructor()
            ->setMethods(array('getfileTypeById', 'getAllfileTypes'))
            ->getMock();

        $fileType = $this->getMockBoxFileTypeModel();

        $mock->expects($this->any())->method('getfileTypeById')->willReturn(null);
        $mock->expects($this->any())->method('getAllfileTypes')->willReturn(array($fileType));

        $this->setComponent(craft(), 'box_fileType', $mock);
    }

    /**
     * Mock Box_FileTypeModel.
     *
     * @return Box_FileTypeModel
     */
    private function getMockBoxFileTypeModel()
    {
        return $this->getMockBuilder('Craft\Box_FileTypeModel')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock uploaded file ($_FILES).
     */
    private function setMockUploadedFile($name, $size)
    {
        $_FILES['box-upload'] = array(
            'name' => $name,
            'size' => $size,
            'tmp_name' => $name,
            'type' => IOHelper::getMimeTypeByExtension($name),
            'error' => null,
        );

        // Reset file cache
        UploadedFile::reset();
    }

    /**
     * Mock Box_ContentService.
     */
    private function setMockBoxContentService()
    {
        $mock = $this->getMockBuilder('Craft\Box_ContentService')
            ->disableOriginalConstructor()
            ->setMethods(array('createClient'))
            ->getMock();

        $accessToken = 'test';
        $api = new \AdammBalogh\Box\Client\Content\ApiClient($accessToken);
        $upload = new \AdammBalogh\Box\Client\Content\UploadClient($accessToken);
        $client = new \AdammBalogh\Box\ContentClient($api, $upload);

        $mock->expects($this->any())->method('createClient')->willReturn($client);

        $this->setComponent(craft(), 'box_content', $mock);
    }
}
