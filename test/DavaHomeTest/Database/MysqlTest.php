<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace DavaHomeTest\Database;

use DavaHome\Database\Adapter\Mysql;
use DavaHome\Database\Extension\CustomOperator;
use DavaHome\Database\Extension\DirectValue;
use Exception;
use Mockery;
use PDOStatement;

function createExecuteMock(int $count = 1): Mysql
{
    $mysqlMock = Mockery::mock(Mysql::class)
        ->makePartial()
        ->allows(['execute']);

    $mysqlMock->expects('execute')->times($count)->andReturnUsing(function ($statement, $inputParameters) {
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

test('update with empty where', function () {
    $this->expectException(Exception::class);
    createExecuteMock(0)->update('', [], []);

})->throws(Exception::class, 'Empty where statements are not allowed!');

test('update', function () {
    $mysql = createExecuteMock();

    $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => 'baz']);
    expect($result->getMockParameters())->toHaveKey('value_0');
    expect($result->getMockParameters())->toHaveKey('where_1');
    expect($result->getMockQuery())->toBe('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` = :where_1');
    expect($result->getMockParameters()['value_0'])->toBe('bar');
    expect($result->getMockParameters()['where_1'])->toBe('baz');
});

test('update with direct value', function () {
    $mysql = createExecuteMock();

    $result = $mysql->update('foobar', ['foo' => new DirectValue('BAR()')], ['foo' => new DirectValue('BAZ()')]);
    expect($result->getMockParameters())->toBeEmpty();
    expect($result->getMockQuery())->toBe(sprintf('UPDATE `foobar` SET `foo` = %s WHERE `foo` = %s', 'BAR()', 'BAZ()'));
});

test('update with custom operator', function () {
    $mysql = createExecuteMock();

    $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', 'baz')]);
    expect($result->getMockParameters())->toHaveKey('value_0');
    expect($result->getMockParameters())->toHaveKey('where_1');
    expect($result->getMockQuery())->toBe('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != :where_1');
    expect($result->getMockParameters()['value_0'])->toBe('bar');
    expect($result->getMockParameters()['where_1'])->toBe('baz');
});

test('update with custom operator and direct value', function () {
    $mysql = createExecuteMock();

    $result = $mysql->update('foobar', ['foo' => 'bar'], ['foo' => new CustomOperator('!=', new DirectValue('BAZ()'))]);
    expect($result->getMockParameters())->toHaveKey('value_0');
    expect($result->getMockQuery())->toBe(sprintf('UPDATE `foobar` SET `foo` = :value_0 WHERE `foo` != %s', 'BAZ()'));
    expect($result->getMockParameters()['value_0'])->toBe('bar');
});

test('insert', function () {
    $mysql = createExecuteMock();

    $result = $mysql->insert('foobar', ['foo' => 'bar']);
    expect($result->getMockParameters())->toHaveKey('value_0');
    expect($result->getMockQuery())->toBe('INSERT INTO `foobar` SET `foo` = :value_0');
    expect($result->getMockParameters()['value_0'])->toBe('bar');
});
