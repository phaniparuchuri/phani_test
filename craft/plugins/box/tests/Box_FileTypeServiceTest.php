<?php

namespace Craft;

/**
 * Box File Type Service Test.
 *
 * Integrates Box with Craft Assets
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2014-2016
 *
 * @link      https://nerds.company
 *
 * @coversDefaultClass Craft\Box_FileTypeService
 * @covers ::<!public>
 */
class Box_FileTypeServiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Set up parent
        parent::setUpBeforeClass();

        // Require dependencies
        require_once __DIR__.'/../services/Box_FileTypeService.php';
        require_once __DIR__.'/../records/Box_FileTypeRecord.php';
        require_once __DIR__.'/../models/Box_FileTypeModel.php';
    }

    /**
     * {@inheritdoc}
     */
    public function teardown()
    {
        parent::teardown();
        Box_FileTypeRecord::$db = craft()->db;
    }

    /**
     * Test get all file type id's.
     *
     * @covers ::getAllfileTypeIds
     */
    final public function testGetAllfileTypeIds()
    {
        $this->setMockDbConnection();

        $service = new Box_FileTypeService();
        $result = $service->getAllfileTypeIds();

        $this->assertCount(2, $result);
    }

    /**
     * Test get all file types.
     *
     * @param string $indexBy
     *
     * @covers ::getAllfileTypes
     * @covers ::getAllfileTypeIds
     * @dataProvider provideIndexBy
     */
    final public function testGetAllfileTypes($indexBy)
    {
        Box_FileTypeRecord::$db = $this->setMockDbConnection();

        $service = new Box_FileTypeService();
        $result = $service->getAllfileTypes($indexBy);

        $this->assertCount(2, $result);

        // Now generate some extra coverage
        $result = $service->getAllfileTypeIds();

        $this->assertCount(2, $result);
    }

    /**
     * Test get total file types.
     *
     * @covers ::getTotalfileTypes
     */
    final public function testGetTotalfileTypes()
    {
        Box_FileTypeRecord::$db = $this->setMockDbConnection();

        $service = new Box_FileTypeService();
        $result = $service->getTotalfileTypes();

        $this->assertEquals(2, $result);
    }

    /**
     * Test get file type by id.
     *
     * @covers ::getfileTypeById
     */
    final public function testGetfileTypeById()
    {
        Box_FileTypeRecord::$db = $this->setMockDbConnection();

        $service = new Box_FileTypeService();
        $result = $service->getFileTypeById(1);

        $this->assertInstanceOf('Craft\Box_FileTypeModel', $result);
    }

    /**
     * Test get file type by handle.
     *
     * @covers ::getfileTypeByHandle
     */
    final public function testGetfileTypeByHandle()
    {
        Box_FileTypeRecord::$db = $this->setMockDbConnection();

        $service = new Box_FileTypeService();
        $result = $service->getFileTypeByHandle('test');

        $this->assertInstanceOf('Craft\Box_FileTypeModel', $result);
    }

    /**
     * Test save file type.
     *
     * @param Box_FileTypeModel $fileType
     *
     * @covers ::savefileType
     * @dataProvider provideSavePaths
     */
    final public function testSavefileType(Box_FileTypeModel $fileType)
    {
        $this->setMockFieldsService();

        $service = new Box_FileTypeService();

        try {
            $result = $service->savefileType($fileType);
            $this->assertFalse($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e);
        }
    }

    /**
     * Test delete file type by id.
     *
     * @param int $fileTypeId
     *
     * @covers ::deletefileTypeById
     * @dataProvider provideDeletePaths
     */
    final public function testDeletefileTypeById($fileTypeId)
    {
        $this->setMockFieldsService();
        $this->setMockElementsService();

        $service = new Box_FileTypeService();

        try {
            $result = $service->deletefileTypeById($fileTypeId);
            $this->assertFalse($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e);
        }
    }

    /**
     * Provide index by.
     *
     * @return array
     */
    final public function provideIndexBy()
    {
        return array(
            'Index by id' => array('id'),
            'Do not index' => array(null),
            'Index by something else' => array('name'),
        );
    }

    /**
     * Provide save paths.
     *
     * @return array
     */
    final public function provideSavePaths()
    {
        require_once __DIR__.'/../models/Box_FileTypeModel.php';

        return array(
            'With id' => array(new Box_FileTypeModel(array(
                'id' => 1,
                'name' => 'Test',
                'handle' => 'test',
            ))),
            'Without id' => array(new Box_FileTypeModel(array(
                'name' => 'Test',
                'handle' => 'test',
            ))),
        );
    }

    /**
     * Provide delete paths.
     *
     * @return array
     */
    final public function provideDeletePaths()
    {
        return array(
            'With id' => array(1),
            'Without id' => array(0),
        );
    }

    /**
     * Mock DbConnection.
     *
     * @return DbConnection
     */
    private function setMockDbConnection()
    {
        $mock = $this->getMockBuilder('Craft\DbConnection')
            ->disableOriginalConstructor()
            ->setMethods(array('createCommand', 'getSchema'))
            ->getMock();
        $mock->autoConnect = false; // Do not auto connect

        $command = $this->getMockDbCommand($mock);
        $schema = $this->getMockDbSchema($mock);

        $mock->expects($this->any())->method('createCommand')->willReturn($command);
        $mock->expects($this->any())->method('getSchema')->willReturn($schema);

        $this->setComponent(craft(), 'db', $mock);

        return $mock;
    }

    /**
     * Mock DbCommand.
     *
     * @param DbConnection $connection
     *
     * @return DbCommand
     */
    private function getMockDbCommand(DbConnection $connection)
    {
        $mock = $this->getMockBuilder('Craft\DbCommand')
            ->setConstructorArgs(array($connection))
            ->setMethods(array('select', 'from', 'queryColumn', 'queryAll', 'queryRow', '__get'))
            ->getMock();

        $record1 = new Box_FileTypeRecord();
        $record1->id = 1;
        $record1->name = 'test 1';
        $record2 = new Box_FileTypeRecord();
        $record2->id = 2;
        $record2->name = 'test 2';

        $mock->expects($this->any())->method('select')->will($this->returnSelf());
        $mock->expects($this->any())->method('from')->will($this->returnSelf());
        $mock->expects($this->any())->method('queryColumn')->willReturn(array('test 1', 'test 2'));
        $mock->expects($this->any())->method('queryAll')->willReturn(array($record1, $record2));
        $mock->expects($this->any())->method('queryRow')->willReturn($record1);
        $mock->expects($this->any())->method('__get')->willReturn(true);

        return $mock;
    }

    /**
     * Mock MysqlSchema.
     *
     * @param DbConncetion $connection
     *
     * @return MysqlSchema
     */
    private function getMockDbSchema(DbConnection $connection)
    {
        $mock = $this->getMockBuilder('Craft\MysqlSchema')
            ->disableOriginalConstructor()
            ->setMethods(array('getTable', 'getCommandBuilder'))
            ->getMock();

        $table = new \CMysqlTableSchema();
        $builder = $this->getMockCommandBuilder($connection);

        $mock->expects($this->any())->method('getTable')->willReturn($table);
        $mock->expects($this->any())->method('getCommandBuilder')->willReturn($builder);

        return $mock;
    }

    /**
     * Mock CdbCommandBuilder.
     *
     * @param DbConnection $connection
     *
     * @return \CdbCommandBuilder
     */
    private function getMockCommandBuilder(DbConnection $connection)
    {
        $mock = $this->getMockBuilder('\CdbCommandBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('createFindCommand', 'createColumnCriteria'))
            ->getMock();

        $command = $this->getMockDbCommand($connection);

        $mock->expects($this->any())->method('createFindCommand')->willReturn($command);
        $mock->expects($this->any())->method('createColumnCriteria')->willReturn($command);

        return $mock;
    }

    /**
     * Mock FieldsService.
     *
     * @return FieldsService
     */
    private function setMockFieldsService()
    {
        $mock = $this->getMockBuilder('Craft\FieldsService')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteLayoutById', 'saveLayout'))
            ->getMock();

        $mock->expects($this->any())->method('deleteLayoutById')->willReturn(true);
        $mock->expects($this->any())->method('saveLayout')->will($this->throwException(new \Exception()));

        $this->setComponent(craft(), 'fields', $mock);
    }

    /**
     * Mock ElementsService.
     *
     * @return ElementsService
     */
    private function setMockElementsService()
    {
        $mock = $this->getMockBuilder('Craft\ElementsService')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteElementById'))
            ->getMock();

        $mock->expects($this->any())->method('deleteElementById')->will($this->throwException(new \Exception()));

        $this->setComponent(craft(), 'elements', $mock);
    }
}
