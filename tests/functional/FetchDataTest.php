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
 * Class OciConnectionTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 11:50 PM
 */
class FetchDataTest extends AbstractFunctionalTestCase
{
    public function testQueryAndFetchAssoc()
    {
        $expected = $this->getConnection()->getRowCount('employees');
        $this->assertEquals(107, $expected, 'Pre-Check');

        $conn = $this->ociConnect();
        $stmt = $conn->query('SELECT * FROM employees');

        $rows = array();

        while (($row = $stmt->fetchAssoc())) {
            $rows[] = $row;
        }

        $this->assertEquals($expected, count($rows), 'Row count is not the same.');
        $this->assertArrayHasKey('EMPLOYEE_ID', $rows[0]);
    }

    public function testPrepareAndFetchAssoc()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT * FROM hr.employees WHERE job_id = :job_id');
        $stmt->bind('job_id')->toValue('ST_CLERK')->asString();
        $stmt->execute();

        $rows = array();

        while (($row = $stmt->fetchAssoc())) {
            $rows[] = $row;
        }

        $this->assertEquals(20, count($rows), 'Row count is not the same.');
        $this->assertArrayHasKey('JOB_ID', $rows[0]);
        $this->assertArrayNotHasKey(0, $rows[0]);
        $this->assertEquals('ST_CLERK', $rows[0]['JOB_ID']);
    }

    public function testFetchAll()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->query('SELECT * FROM hr.employees');
        $rows = $stmt->fetchAll();

        $this->assertEquals(107, count($rows), 'Row count is not the same.');
        $this->assertArrayHasKey('EMPLOYEE_ID', $rows[0]);
    }

    public function testFetchColumn()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->query('SELECT employee_id FROM hr.employees');

        $ids = array();

        while (($id = $stmt->fetchColumn())) {
            $ids[] = $id;
        }

        $this->assertEquals(107, count($ids), 'Row count is not the same.');
        $this->assertTrue(ctype_digit($ids[0]), 'Invalid employee ID.');
        $this->assertGreaterThan(0, $ids[0], 'Invalid employee ID.');
    }
}
