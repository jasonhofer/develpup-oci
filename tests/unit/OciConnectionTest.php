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
use Develpup\Oci\OciException;

/**
 * Class OciConnectionTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 6:05 PM
 */
class OciConnectionTest extends AbstractUnitTestCase
{
    public function testBadConnectionThrowsOciException()
    {
        $this->setExpectedException('Develpup\Oci\OciException');

        new OciConnection('bad username', 'bad password', 'bad dsn');
    }

    public function testCanConnectToDatabase()
    {
        try {
            $conn = $this->ociConnect();
        } catch (OciException $e) {
            $conn = null;
        }

        $this->assertInstanceOf('Develpup\Oci\OciConnection', $conn);
    }

    public function testPrepareReturnsOciStatement()
    {
        $conn = $this->ociConnect();

        $this->assertInstanceOf('Develpup\Oci\OciStatement', $conn->prepare('SELECT * FROM employees'));
    }

    public function testQuote()
    {
        $conn = $this->ociConnect();

        $this->assertSame(42, $conn->quote(42));
        $this->assertSame(4.2, $conn->quote(4.2));
        $this->assertSame("'foo'", $conn->quote('foo'));
        $this->assertSame("'foo ''bar'''", $conn->quote("foo 'bar'"));
        $this->assertSame("'\\\\foo\\\\bar\\\\'", $conn->quote('\\foo\\bar\\'));
        $this->assertSame("'\\000\\n\\r\\\\\\032'", $conn->quote("\000\n\r\\\032"));
    }

    public function testQueryReturnsOciStatement()
    {
        $conn = $this->ociConnect();

        $this->assertInstanceOf('Develpup\Oci\OciStatement', $conn->query('SELECT * FROM employees'));
    }
}
