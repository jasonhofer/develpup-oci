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

/**
 * Class OciStatementTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 11:25 PM
 */
class OciStatementTest extends AbstractUnitTestCase
{
    public function testBindMethodReturnTypes()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT * FROM employees WHERE job_id IN (:p1, :p2)');

        $this->assertInstanceOf('Develpup\Oci\Contract\OciBindToInterface', $stmt->bind('p1'));
        $this->assertInstanceOf('Develpup\Oci\Contract\OciBindAsInterface', $stmt->bind('p1')->toValue(1));
        $this->assertInstanceOf('Develpup\Oci\Contract\OciBindAsInterface', $stmt->bind('p2')->toVar($p2));
    }
}
