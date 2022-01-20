<?php
declare(strict_types=1);

namespace DavaHomeTest\Database;

use DavaHome\Database\Adapter\Mysql;
use DavaHome\Database\Extension\CustomOperator;
use DavaHome\Database\Extension\DirectValue;
use Exception;
use PDOStatement;
use PHPUnit_Framework_TestCase;

class MysqlTest extends PHPUnit_Framework_TestCase
{
    protected function getMysqlMock_Execute(int $count = 1): Mysql
    {
        $mysqlMock = $this->getMockBuilder(Mysql::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $mysqlMock->expects($this->exactly($count))
            ->method('execute')
            ->willReturnCallback(function ($statement, $inputParameters) {
                return new class($statement, $inputParameters) extends PDOStatement {
                    private string $query;
                    private array $inputParameters;

                    public function __construct(string $query, array $inputParameters)
                    {
                        $this->query = $query;
                        $this->inputParameters = $inputParameters;
                    }


                    public function getMockQuery(): string
                    {
                        return $this->query;
                    }

                    public function getMockParameters(): array
                    {
                        return $this->inputParameters;
                    }
                };
            });

        return $mysqlMock;
    }

    public function testUpdate_WithEmptyWhere(): void
    {
        $this->setExpectedExceptionRegExp(Exception::class, '/empty where/i');
        $this->getMysqlMock_Execute(0)->update('', [], []);
    }

    public function testUpdate(): void
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => 'baz']);
        $this->assertArrayHasKey('value_0', $result->getMockParameters());
        $this->assertArrayHasKey('where_1', $result->getMockParameters());
        $this->assertEquals('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` = :where_1', $result->getMockQuery());
        $this->assertEquals('bar', $result->getMockParameters()['value_0']);
        $this->assertEquals('baz', $result->getMockParameters()['where_1']);
    }

    public function testInsert(): void
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->insert('foobar', ['foo' => 'bar']);
        $this->assertArrayHasKey('value_0', $result->getMockParameters());
        $this->assertEquals('INSERT INTO `foobar` SET `foo` = :value_0', $result->getMockQuery());
        $this->assertEquals('bar', $result->getMockParameters()['value_0']);
    }

    public function testUpdate_WithDirectValue(): void
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => new DirectValue('BAR()')], ['foo' => new DirectValue('BAZ()')]);
        $this->assertEmpty($result->getMockParameters());
        $this->assertEquals(sprintf('UPDATE `foobar` SET `foo` = %s WHERE `foo` = %s', 'BAR()', 'BAZ()'), $result->getMockQuery());
    }

    public function testUpdate_WithCustomOperator(): void
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', 'baz')]);
        $this->assertArrayHasKey('value_0', $result->getMockParameters());
        $this->assertArrayHasKey('where_1', $result->getMockParameters());
        $this->assertEquals('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != :where_1', $result->getMockQuery());
        $this->assertEquals('bar', $result->getMockParameters()['value_0']);
        $this->assertEquals('baz', $result->getMockParameters()['where_1']);
    }

    public function testUpdate_WithCustomOperatorAndDirectValue(): void
    {
        $mysql = $this->getMysqlMock_Execute();

        $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', new DirectValue('BAZ()'))]);
        $this->assertArrayHasKey('value_0', $result->getMockParameters());
        $this->assertEquals(sprintf('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != %s', 'BAZ()'), $result->getMockQuery());
        $this->assertEquals('bar', $result->getMockParameters()['value_0']);
    }
}
