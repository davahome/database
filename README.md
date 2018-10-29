# davahome/database

[![Build Status](https://travis-ci.org/DavaHome/database.svg?branch=master)](https://travis-ci.org/DavaHome/database)

davahome/database is a small php library which provides a very simple PDO based MySQL wrapper. Its main functionality is to provide some additional functionality to the basic PDO object.

The `DavaHome\Database\MySQL` class is directly derived from PDO and provides all of its methods. There are some additional features like:

- **PDO Statement Cache** (Reuse of PDO statements if the query hasn't changed)
- **[Basic operations as methods](#basic-operation-methods)** (Like select, delete, and more)

# Installation

```bash
php composer.phar require davahome/database
```




# Basic Operation Methods

These methods are forced by the `DavaHome\Database\DatabaseInterface` and are supported by all database handlers

### select

Select rows from database. The where statement is an associative array with `tableColumn => value` convention.

```php
/**
 * Select from database
 *
 * @param string $table
 * @param array  $where
 *
 * @return mixed
 */
public function select($table, array $where)
```


### update

Update existing rows in the database. The values are an associative array, exactly like the `where` statement from the [select](#select) method.

```php
/**
 * Update a row
 *
 * @param string $table
 * @param array  $values key=>value
 * @param array  $where  key=>value where condition (will be combined using AND)
 * @param bool   $allowEmptyWhere
 *
 * @return mixed
 * @throws \Exception
 */
public function update($table, array $values, array $where, $allowEmptyWhere = false)
```


### insert

Insert a new row into the database. The values are an associative array, exactly like the `where` statement from the [select](#select) method.

```php
/**
 * Insert a new row
 *
 * @param string $table
 * @param array  $values key=>value
 *
 * @return mixed
 */
public function insert($table, array $values)
```


### delete

Delete existing rows from the database. The where statement is identical to the [select](#select) method.

```php
/**
 * Delete from database
 *
 * @param string $table
 * @param array  $where
 * @param bool   $allowEmptyWhere
 *
 * @return mixed
 * @throws \Exception
 */
public function delete($table, array $where, $allowEmptyWhere = false)
```




# Mysql

### Example

```php
use DavaHome\Database\Mysql;

$db = Mysql::create(
    Mysql::DRIVER_MYSQL,
    'localhost',
    'root',
    '',
    'database',
    [
        Mysql::ATTR_DEFAULT_FETCH_MODE => Mysql::FETCH_ASSOC,
        Mysql::ATTR_AUTOCOMMIT         => 1,
    ]
);

// Select row
$pdoStatement = $db->select('table', ['id' => 1]); // Returns \PDOStatement

// Update row
$pdoStatement = $db->update('table', ['foo' => 'bar'], ['id' => 1]); // Returns \PDOStatement

// Insert row
$pdoStatement = $db->insert('table', ['foo' => 'bar']); // Returns \PDOStatement

// Delete row
$pdoStatement = $db->delete('table', ['id' => 1]); // Returns \PDOStatement
```

### Additional Mysql methods


#### createUuid

This method creates a uuid which can be used as non-incremental unique index. See the [MySQL documentation](https://dev.mysql.com/doc/refman/5.7/en/miscellaneous-functions.html#function_uuid) for further information.

```php
/**
 * Let the database create a UUID
 *
 * @return string
 */
public function createUuid()
```


#### execute

Creates a prepared statement which will be executed directly

```php
/**
 * Create and execute a prepared statement immediately
 *
 * @param string $statement
 * @param array  $inputParameters
 * @param array  $driverOptions
 *
 * @return mixed|\PDOStatement
 */
public function execute($statement, array $inputParameters = [], array $driverOptions = [])
```


#### setIsolationLevel

Set the isolation level of transactions in the current connection

```php
/**
 * Set the isolation level
 *
 * @param string $isolationLevel
 *
 * @return bool
 */
public function setIsolationLevel($isolationLevel)
```
 


# Advanced queries

To provide a more advanced functionality for the basic operation methods there are additional classes.


### DirectValue

The DirectValue class allows to use MySQL functions or a increment-queries through the basic operation methods.
All arguments given to the DirectValue class will be passed 1-2-1 into the query. There will be no escaping for those values!

```php
use Davahome\Database\DirectValue;

// The query will look like this: UPDATE `table` SET `last_updated` = NOW() WHERE `id` = 1
$db->update('table', ['last_updated' => new DirectValue('NOW()')], ['id' => 1]);

// The query will look like this: UPDATE `table` SET `count` = `count` + 1 WHERE `id` = 1
$db->update('table', ['count' => new DirectValue('`count` + 1')], ['id' => 1]);
```


### CustomOperator

The CustomOperator class allows to override the default operator used by all basic operation methods (`=`). You can also combine the CustomOperator with the DirectValue class.

```php
use Davahome\Database\CustomOperator;

// The query will look like this: SELECT * FROM `table` WHERE `count` >= 2
$db->select('table', ['count' => new CustomOperator('>=', 2)]);

// The query will look like this: SELECT * FROM `table` WHERE `last_updated` <= NOW()
$db->select('table', ['last_updated' => new CustomOperator('<=', new DirectValue('NOW()'))]);
```
