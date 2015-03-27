<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\TestUtils;

/**
 * Class ArrayDataSet
 *
 * @package Develpup\TestUtils
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-27 10:03 AM
 */
class ArrayDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * @var \PHPUnit_Extensions_Database_DataSet_DefaultTable[]
     */
    protected $tables = array();

    /**
     * Example of data passed to the constructor:
     *
     * <code>
     * array(
     *     'users' => array(
     *         array('id' => 1, 'username' => 'joe',  'password' => 'Sup3rS3cr3t'),
     *         array('id' => 1, 'username' => 'bob',  'password' => 'P@55w0rd'),
     *     ),
     *     'guestbook' => array(
     *         array('id' => 1, 'content' => 'Hello buddy!', 'user_id' => 1,    'created' => '2010-04-24 17:15:23'),
     *         array('id' => 2, 'content' => 'I like it!',   'user_id' => null, 'created' => '2010-04-26 12:14:20'),
     *     ),
     * )
     * </code>
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $tableName => $rows) {
            $columns = array();
            if (isset($rows[0])) {
                $columns = array_keys($rows[0]);
            }

            $metaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);
            $table    = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);

            foreach ($rows AS $row) {
                $table->addRow($row);
            }

            $this->tables[$tableName] = $table;
        }
    }

    /**
     * @param bool $reverse
     *
     * @return \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator
     */
    protected function createIterator($reverse = false)
    {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }

    /**
     * @param string $tableName
     *
     * @return mixed
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \InvalidArgumentException("{$tableName} is not a table in the current database.");
        }

        return $this->tables[$tableName];
    }
}
