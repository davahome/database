<?php

namespace DavaHomeTest\Database;

use DavaHome\Database\CustomOperator;
use DavaHome\Database\DirectValue;
use DavaHome\Database\Mysql;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Mysql
     */
    protected function getMysqlMock_Execute($count = 1)
    {
        $mysqlMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $mysqlMock->expects($this->exactly($count))
            ->method('execute')
            ->willReturnCallback(function ($statement, $inputParameters) {
                return ['query' => $statement, 'params' => $inputParameters];
            });

        return $mysqlMock;
    }

    public function testUpdate_WithEmptyWhere()
    {
        $this->setExpectedExceptionRegExp(\Exception::class, '/empty where/i');
        $this->getMysqlMock_Execute(0)->update('', [], []);
    }

    public function testUpdate()
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => 'baz']);
        $this->assertArrayHasKey('value_0', $result['params']);
        $this->assertArrayHasKey('where_1', $result['params']);
        $this->assertEquals('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` = :where_1', $result['query']);
        $this->assertEquals('bar', $result['params']['value_0']);
        $this->assertEquals('baz', $result['params']['where_1']);
    }

    public function testInsert()
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->insert('foobar', ['foo' => 'bar']);
        $this->assertArrayHasKey('value_0', $result['params']);
        $this->assertEquals('INSERT INTO `foobar` SET `foo` = :value_0', $result['query']);
        $this->assertEquals('bar', $result['params']['value_0']);
    }

    public function testUpdate_WithDirectValue()
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => new DirectValue('BAR()')], ['foo' => new DirectValue('BAZ()')]);
        $this->assertEmpty($result['params']);
        $this->assertEquals('UPDATE `foobar` SET `foo` = BAR() WHERE `foo` = BAZ()', $result['query']);
    }

    public function testUpdate_WithCustomOperator()
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', 'baz')]);
        $this->assertArrayHasKey('value_0', $result['params']);
        $this->assertArrayHasKey('where_1', $result['params']);
        $this->assertEquals('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != :where_1', $result['query']);
        $this->assertEquals('bar', $result['params']['value_0']);
        $this->assertEquals('baz', $result['params']['where_1']);
    }

    public function testUpdate_WithCustomOperatorAndDirectValue()
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', new DirectValue('BAZ()'))]);
        $this->assertArrayHasKey('value_0', $result['params']);
        $this->assertEquals('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != BAZ()', $result['query']);
        $this->assertEquals('bar', $result['params']['value_0']);
    }
}
