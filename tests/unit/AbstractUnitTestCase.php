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
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractUnitTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 11:56 PM
 */
abstract class AbstractUnitTestCase extends PHPUnit_Framework_TestCase
{
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
