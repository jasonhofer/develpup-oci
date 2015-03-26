<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\Test\Oci;

use Develpup\Oci\OciConnection;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_TestCase;

/**
 * Class AbstractFunctionalTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 11:51 PM
 */
abstract class AbstractFunctionalTestCase extends PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    /**
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                $dsn       = $GLOBALS['DB_DSN'] ?: sprintf('oci:dbname=//%s:%d/%s', $GLOBALS['DB_HOST'], $GLOBALS['DB_PORT'], $GLOBALS['DB_NAME']);
                self::$pdo = new \PDO($dsn, $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_NAME']);
        }

        return $this->conn;
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__ . '/fixture.xml');
    }

    /**
     * @return OciConnection
     */
    protected function ociConnect()
    {
        static $ociConn;

        return $ociConn ?: ($ociConn = $this->ociNewConnect());
    }

    /**
     * @param string|null $charset
     * @param int|null    $sessionMode
     * @param bool        $persistent
     *
     * @return OciConnection
     */
    protected function ociNewConnect($charset = null, $sessionMode = null, $persistent = false)
    {
        return new OciConnection(
            $GLOBALS['DB_USER'],
            $GLOBALS['DB_PASS'],
            $GLOBALS['DB_DSN'] ?: $GLOBALS['DB_HOST'] . '/' . $GLOBALS['DB_NAME'],
            $charset,
            $sessionMode,
            $persistent
        );
    }

    /**
     * @param string $table
     */
    protected function assertTableExists($table)
    {
        $tables = array_map('strtolower', $this->getConnection()->getMetaData()->getTableNames());
        if (!in_array(strtolower($table), $tables)) {
            $this->fail("Failed asserting that the database contains table \"{$table}\".");
        }
    }

    /**
     * @param string $name
     */
    protected function assertTableNotExists($name)
    {
        $tables = array_map('strtolower', $this->getConnection()->getMetaData()->getTableNames());
        if (in_array(strtolower($name), $tables)) {
            $this->fail("Failed asserting that the database does not contain table \"{$name}\".");
        }
    }

    /**
     * @param string $name
     *
     * @return int
     */
    protected function dropTableIfExists($name)
    {
        return $this->dropIfExists('table', $name);
    }

    /**
     * @param string $name
     *
     * @return int
     */
    protected function dropProcedureIfExists($name)
    {
        return $this->dropIfExists('procedure', $name);
    }

    /**
     * @see http://stackoverflow.com/questions/1799128/oracle-if-table-exists
     *
     * @param string $type
     * @param string $name
     *
     * @return int
     */
    private function dropIfExists($type, $name)
    {
        static $codes = array(
            'TABLE'             => -942,
            'VIEW'              => -942,
            'INDEX'             => -1418,
            'TYPE'              => -1918,
            'USER'              => -1918,
            'DATABASE LINK'     => -2024,
            'SEQUENCE'          => -2289,
            'FUNCTION'          => -4043,
            'PACKAGE'           => -4043,
            'PROCEDURE'         => -4043,
            'TRIGGER'           => -4080,
            'MATERIALIZED VIEW' => -12003,
        );

        $type = strtoupper($type);
        $code = $codes[$type];

        return $this->getConnection()->getConnection()->exec("
            BEGIN
                EXECUTE IMMEDIATE 'DROP {$type} {$name}';
            EXCEPTION
               WHEN OTHERS THEN
                  IF SQLCODE != {$code} THEN
                     RAISE;
                  END IF;
            END;
        ");
    }
}
