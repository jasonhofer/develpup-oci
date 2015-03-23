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

        return $ociConn ?: ($ociConn = new OciConnection(
            $GLOBALS['DB_USER'],
            $GLOBALS['DB_PASS'],
            $GLOBALS['DB_DSN'] ?: $GLOBALS['DB_HOST'] . '/' . $GLOBALS['DB_NAME']
        ));
    }
}
