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
use Develpup\Oci\OciDescriptor;
use Develpup\Oci\OciRowId;

/**
 * Class PhpManualOciExamplesTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-23 3:39 PM
 */
class PhpManualOciExamplesTest extends AbstractFunctionalTestCase
{
    const TOTAL_EMPLOYEES       = 107;
    const TOTAL_EMPLOYEE_FIELDS = 11;

    /******************************************************************************************************************\
     * oci_connect()
     *
     * @see http://us.php.net/manual/en/function.oci-connect.php
    \******************************************************************************************************************/

    /**
     * Example #1 Basic oci_connect() using Easy Connect syntax
     *
     * Note: This is just testing the connection itself, it is not checking if you are using the "Easy Connect" syntax.
     */
    public function test_oci_connect_example_1()
    {
        // Connects to the XE service (i.e. database) on the "localhost" machine
        $conn = $this->ociNewConnect();

        $stmt = $conn->query('SELECT * FROM employees');
        $stmt->execute();
        $rows = array();

        while (($row = $stmt->fetchArray(OCI_ASSOC | OCI_RETURN_NULLS))) {
            $rows[] = $row;
        }

        $firstTwoRows = array(
            array(
                'EMPLOYEE_ID'    => '100',
                'FIRST_NAME'     => 'Steven',
                'LAST_NAME'      => 'King',
                'EMAIL'          => 'SKING',
                'PHONE_NUMBER'   => '515.123.4567',
                'HIRE_DATE'      => '17-JUN-03',
                'JOB_ID'         => 'AD_PRES',
                'SALARY'         => '24000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => null,
                'DEPARTMENT_ID'  => '90',
            ),
            array(
                'EMPLOYEE_ID'    => '101',
                'FIRST_NAME'     => 'Neena',
                'LAST_NAME'      => 'Kochhar',
                'EMAIL'          => 'NKOCHHAR',
                'PHONE_NUMBER'   => '515.123.4568',
                'HIRE_DATE'      => '21-SEP-05',
                'JOB_ID'         => 'AD_VP',
                'SALARY'         => '17000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => '100',
                'DEPARTMENT_ID'  => '90',
            )
        );

        $this->assertCount(self::TOTAL_EMPLOYEES, $rows);
        $this->assertCount(self::TOTAL_EMPLOYEE_FIELDS, $rows[0]);
        $this->assertSame($firstTwoRows, array($rows[0], $rows[1]));
    }

    /**
     * Example #2 Basic oci_connect() using a Network Connect name
     *
     * @TODO
     */
//    public function test_oci_connect_example_2()
//    {
//    }

    /**
     * Example #3 oci_connect() with an explicit character set
     */
    public function test_oci_connect_example_3()
    {
        $conn = $this->ociNewConnect('AL32UTF8');

        $stmt = $conn->query('SELECT * FROM employees');
        $stmt->execute();
        $rows = array();

        while (($row = $stmt->fetchArray(OCI_ASSOC | OCI_RETURN_NULLS))) {
            $rows[] = $row;
        }

        $firstTwoRows = array(
            array(
                'EMPLOYEE_ID'    => '100',
                'FIRST_NAME'     => 'Steven',
                'LAST_NAME'      => 'King',
                'EMAIL'          => 'SKING',
                'PHONE_NUMBER'   => '515.123.4567',
                'HIRE_DATE'      => '17-JUN-03',
                'JOB_ID'         => 'AD_PRES',
                'SALARY'         => '24000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => null,
                'DEPARTMENT_ID'  => '90',
            ),
            array(
                'EMPLOYEE_ID'    => '101',
                'FIRST_NAME'     => 'Neena',
                'LAST_NAME'      => 'Kochhar',
                'EMAIL'          => 'NKOCHHAR',
                'PHONE_NUMBER'   => '515.123.4568',
                'HIRE_DATE'      => '21-SEP-05',
                'JOB_ID'         => 'AD_VP',
                'SALARY'         => '17000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => '100',
                'DEPARTMENT_ID'  => '90',
            )
        );

        $this->assertCount(self::TOTAL_EMPLOYEES, $rows);
        $this->assertCount(self::TOTAL_EMPLOYEE_FIELDS, $rows[0]);
        $this->assertSame($firstTwoRows, array($rows[0], $rows[1]));
    }

    /**
     * Example #4 Using multiple calls to oci_connect()
     */
    public function test_oci_connect_example_4()
    {
        $self = $this;

        $this->dropTableIfExists('hallo');

        $c1 = $this->ociNewConnect();
        $c2 = $this->ociNewConnect();

        // Both $c1 and $c2 should show the same PHP resource id meaning they use the
        // same underlying database connection
        $c1res = (string) $c1->getResource();
        $c2res = (string) $c2->getResource();
        $this->assertRegExp('/^Resource id #\d+$/', $c1res);
        $this->assertRegExp('/^Resource id #\d+$/', $c2res);
        $this->assertSame($c1res, $c2res, 'Connection resource strings did not match.');

        $create_table = function (OciConnection $conn) use ($self) {
            $self->assertTableNotExists('hallo');
            $conn->exec('CREATE TABLE hallo (test VARCHAR2(64))');
            $self->assertTableExists('hallo');
        };

        $drop_table = function (OciConnection $conn) use ($self) {
            $self->assertTableExists('hallo');
            $conn->exec('DROP TABLE hallo');
            $self->assertTableNotExists('hallo');
        };

        $insert_data = function (OciConnection $conn, $value) use ($self) {
            $conn->beginTransaction();
            $conn->exec(str_replace(':value', $conn->quote($value), "INSERT INTO hallo VALUES(:value)"));
        };

        $select_data = function ($connName, OciConnection $conn, $hasData = true) use ($self) {
            $stmt = $conn->query('SELECT * FROM hallo');
            $rows = array();

            while (($row = $stmt->fetchAssoc())) {
                $rows[] = $row;
            }

            if ($hasData) {
                $expected = array(array('TEST' => 'foo'), array('TEST' => 'bar'));
                $self->assertSame($expected, $rows, "Connection '{$connName}' did not return expected row data.");
            } else {
                $self->assertEmpty($rows, "After rolling back, connection '{$connName}' did not return an empty result.");
            }
        };

        $create_table($c1);

        $insert_data($c1, 'foo');   // Insert a row using c1
        $insert_data($c2, 'bar');   // Insert a row using c2

        $select_data('c1', $c1, true);   // Results of both inserts are returned
        $select_data('c2', $c2, true);   // Results of both inserts are returned

        $self->assertTableRowCount('hallo', 0, "Before committing the inserts, table 'hallo' should have no rows." );

        $c1->rollback(); // Rollback using c1

        $select_data('c1', $c1, false);   // Both inserts have been rolled back
        $select_data('c2', $c2, false);

        $insert_data($c1, 'foo');   // Insert a row using c1
        $insert_data($c2, 'bar');   // Insert a row using c2

        $select_data('c1', $c1, true);   // Results of both inserts are returned
        $select_data('c2', $c2, true);   // Results of both inserts are returned

        $self->assertTableRowCount('hallo', 0, "Before committing the inserts, table 'hallo' should have no rows." );

        $c1->commit(); // Commit using c1

        $select_data('c1', $c1, true);   // Both inserts have been rolled back
        $select_data('c2', $c2, true);

        $self->assertTableRowCount('hallo', 2, "After committing the inserts, table 'hallo' should have two rows." );

        $drop_table($c1);

        // Closing one of the connections makes the PHP resource unusable, but
        // the other could be used
        $this->assertTrue($c1->close(), "Connection 'c1' failed to close.");

        $this->assertEmpty((string) $c1->getResource(), "Connection 'c1' failed to free connection resource.");
        $this->assertSame($c2res, (string) $c2->getResource(), "Connection 'c1' should still be open.");
    }


    /******************************************************************************************************************\
     * oci_pconnect()
     *
     * @see http://us.php.net/manual/en/function.oci-pconnect.php
    \******************************************************************************************************************/

    /**
     * Example #1 Basic oci_connect() using Easy Connect syntax
     *
     * Note: This is just testing the connection itself, it is not checking if you are using the "Easy Connect" syntax.
     */
    public function test_oci_pconnect_example_1()
    {
        // Connects to the XE service (i.e. database) on the "localhost" machine
        $conn = $this->ociNewConnect(null, null, true);

        $stmt = $conn->query('SELECT * FROM employees');
        $stmt->execute();
        $rows = array();

        while (($row = $stmt->fetchArray(OCI_ASSOC | OCI_RETURN_NULLS))) {
            $rows[] = $row;
        }

        $firstTwoRows = array(
            array(
                'EMPLOYEE_ID'    => '100',
                'FIRST_NAME'     => 'Steven',
                'LAST_NAME'      => 'King',
                'EMAIL'          => 'SKING',
                'PHONE_NUMBER'   => '515.123.4567',
                'HIRE_DATE'      => '17-JUN-03',
                'JOB_ID'         => 'AD_PRES',
                'SALARY'         => '24000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => null,
                'DEPARTMENT_ID'  => '90',
            ),
            array(
                'EMPLOYEE_ID'    => '101',
                'FIRST_NAME'     => 'Neena',
                'LAST_NAME'      => 'Kochhar',
                'EMAIL'          => 'NKOCHHAR',
                'PHONE_NUMBER'   => '515.123.4568',
                'HIRE_DATE'      => '21-SEP-05',
                'JOB_ID'         => 'AD_VP',
                'SALARY'         => '17000',
                'COMMISSION_PCT' => null,
                'MANAGER_ID'     => '100',
                'DEPARTMENT_ID'  => '90',
            )
        );

        $this->assertCount(self::TOTAL_EMPLOYEES, $rows);
        $this->assertCount(self::TOTAL_EMPLOYEE_FIELDS, $rows[0]);
        $this->assertSame($firstTwoRows, array($rows[0], $rows[1]));
    }


    /******************************************************************************************************************\
     * oci_parse()
     *
     * @see http://us.php.net/manual/en/function.oci-parse.php
    \******************************************************************************************************************/

    /**
     * Example #1 oci_parse() example for SQL statements
     *
     * Same as oci_connect() example #1
     */
//- public function test_oci_parse_example_1()
//- {
//- }

    /**
     * Example #2 oci_parse() example for PL/SQL statements
     */
    public function test_oci_parse_example_2()
    {
        $this->getConnection()->getConnection()->exec("
            CREATE OR REPLACE PROCEDURE x2 (
                p1 IN NUMBER,
                p2 OUT NUMBER
            ) AS BEGIN
                p2 := p1 * 2;
            END;
        ");

        $conn = $this->ociConnect();

        $stmt = $conn->prepare('BEGIN x2(:p1, :p2); END;');

        $stmt->bind('p1')->toValue(8)->asInt();
        $stmt->bind('p2')->toVar($p2)->asInt(40);

        $stmt->execute();

        $this->assertSame(16, $p2, 'Output variable did not contain the expected value.');

        $stmt->close();

        $this->dropProcedureIfExists('x2');
    }


    /******************************************************************************************************************\
     * oci_execute()
     *
     * @see http://us.php.net/manual/en/function.oci-execute.php
    \******************************************************************************************************************/

    /**
     * Example #1 oci_execute() for queries
     *
     * Same as oci_connect() example #1
     */
//- public function test_oci_execute_example_1()
//- {
//- }

    /**
     * Example #2 oci_execute() without specifying a mode example
     */
    public function test_oci_execute_example_2()
    {
        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (col1 NUMBER)');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');

        $conn = $this->ociConnect();
        $conn->exec('INSERT INTO my_table (col1) VALUES (123)'); // The row is committed and immediately visible to other users

        $this->assertTableRowCount('my_table', 1);

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #3 oci_execute() with OCI_NO_AUTO_COMMIT example
     */
    public function test_oci_execute_example_3()
    {
        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (col1 NUMBER)');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');

        $conn = $this->ociConnect();
        $conn->beginTransaction();
        $stmt = $conn->prepare('INSERT INTO my_table (col1) VALUES (:bv)');
        $stmt->bind('bv')->toVar($i)->asInt();
        $count = 5;
        for ($i = 1; $i <= $count; ++$i) {
            $stmt->execute();
        }
        $this->assertTableRowCount('my_table', 0, 'Pre-commit condition');
        $conn->commit();

        $this->assertTableRowCount('my_table', $count);

        $stmt = $conn->query('SELECT col1 FROM my_table');
        $vals = array();
        while (($val = $stmt->fetchColumn())) {
            $vals[] = (int) $val;
        }

        $this->assertSame(range(1, $count), $vals);

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #4 oci_execute() with different commit modes example
     *
     * @TODO can this even be done using this system?
     */
//- public function test_oci_execute_example_4()
//- {
//- }

    /**
     * Example #5 oci_execute() with OCI_DESCRIBE_ONLY example
     */
    public function test_oci_execute_example_5()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->query('SELECT * FROM locations');
        $conn->describe($stmt); // statement is executed by the connection.

        $results = array();
        for ($i = 1; $i <= $stmt->columnCount(); ++$i) {
            $results[] = $stmt->columnName($i);
        }

        $expected = array (
            'LOCATION_ID',
            'STREET_ADDRESS',
            'POSTAL_CODE',
            'CITY',
            'STATE_PROVINCE',
            'COUNTRY_ID',
        );

        $this->assertSame($expected, $results);
    }


    /******************************************************************************************************************\
     * oci_bind_by_name()
     *
     * @see http://us.php.net/manual/en/function.oci-bind-by-name.php
    \******************************************************************************************************************/

    /**
     * Example #1 Inserting data with oci_bind_by_name()
     */
    public function test_oci_bind_by_name_example_1()
    {
        // Create the table with:
        //   CREATE TABLE my_table (id NUMBER, text VARCHAR2(40));

        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (id NUMBER, text VARCHAR2(40))');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');

        $conn = $this->ociConnect();
        $stmt = $conn->prepare('INSERT INTO my_table (id, text) VALUES(:id_bv, :text_bv)');
        $stmt->bind('id_bv')->toValue(1)->asInt();
        $stmt->bind('text_bv')->toValue('Data to insert     ')->asString();
        $stmt->execute();

        // Table now contains: 1, 'Data to insert     '

        $this->assertTableRowCount('my_table', 1);

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #2 Binding once for multiple
     */
    public function test_oci_bind_by_name_example_2()
    {
        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (id NUMBER, text VARCHAR2(40))');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');

        $conn = $this->ociConnect();

        $numbers = array(1, 3, 5, 7, 11);  // data to insert
        $count   = count($numbers);

        $stmt = $conn->prepare('INSERT INTO my_table (id) VALUES (:bv)');
        $stmt->bind('bv')->toVar($v)->asInt(20);

        $conn->beginTransaction();
        foreach ($numbers as $v) {
            $stmt->execute();
        }
        $conn->commit(); // commit everything at once

        // Table contains five rows: 1, 3, 5, 7, 11
        $this->assertTableRowCount('my_table', $count);

        $stmt   = $conn->query('SELECT id FROM my_table');
        $values = array();
        while (($val = $stmt->fetchColumn())) {
            $values[] = (int) $val;
        }

        $this->assertSame($numbers, $values);

        $stmt->close();

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #3 Binding with a foreach() loop
     */
    public function test_oci_bind_by_name_example_3()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT * FROM departments WHERE department_name = :dname AND location_id = :loc');

        $ba = array(':dname' => 'IT Support', ':loc' => 1700);

        foreach ($ba as $key => $val) {
            // no need to worry about using $va[$key] instead of just $val when using this system.
            $stmt->bind($key)->toValue($val);
        }

        $stmt->execute();
        $row = $stmt->fetchArray(OCI_ASSOC | OCI_RETURN_NULLS);

        $expected = array(
            'DEPARTMENT_ID'   => '210',
            'DEPARTMENT_NAME' => 'IT Support',
            'MANAGER_ID'      => null,
            'LOCATION_ID'     => '1700',
        );

        $this->assertSame($expected, $row);

        $stmt->close();
    }

    /**
     * Example #4 Binding in a WHERE clause
     */
    public function test_oci_bind_by_name_example_4()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT last_name FROM employees WHERE department_id = :didbv ORDER BY last_name');
        $stmt->bind(':didbv')->toValue(60)->asInt();
        $stmt->execute();

        $lastNames = array();
        while (($row = $stmt->fetchAssoc())) {
            $lastNames[] = $row['LAST_NAME'];
        }

        $this->assertSame(array('Austin', 'Ernst', 'Hunold', 'Lorentz', 'Pataballa'), $lastNames);

        $stmt->close();
    }

    /**
     * Example #5 Binding with a LIKE clause
     */
    public function test_oci_bind_by_name_example_5()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT city FROM locations WHERE city LIKE :bv');
        $stmt->bind(':bv')->toValue('South%')->asString();
        $stmt->execute();

        $rows   = $stmt->fetchAll();
        $cities = array();
        foreach ($rows as $row) {
            $cities[] = $row['CITY'];
        }

        $this->assertSame(array('South Brunswick', 'South San Francisco', 'Southlake'), $cities);

        $stmt->close();
    }

    /**
     * Example #6 Binding with REGEXP_LIKE
     */
    public function test_oci_bind_by_name_example_6()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT city FROM locations WHERE REGEXP_LIKE(city, :bv)');
        $stmt->bind(':bv')->toValue('.*ing.*')->asString();
        $stmt->execute();

        $rows   = $stmt->fetchAll();
        $cities = array();
        foreach ($rows as $row) {
            $cities[] = $row['CITY'];
        }

        $this->assertSame(array('Beijing', 'Singapore'), $cities);

        $stmt->close();
    }

    /**
     * Example #7 Binding Multiple Values in an IN Clause
     */
    public function test_oci_bind_by_name_example_7()
    {
        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT last_name FROM employees WHERE employee_id in (:e1, :e2, :e3)');
        $stmt->bind('e1')->toValue(103)->asInt();
        $stmt->bind('e2')->toValue(104)->asInt();
        $stmt->bind('e3')->toValue(null)->asInt();
        $stmt->execute();

        $rows  = $stmt->fetchAll();
        $names = array();
        foreach ($rows as $row) {
            $names[] = $row['LAST_NAME'];
        }

        $this->assertSame(array('Hunold', 'Ernst'), $names);

        $stmt->close();
    }

    /**
     * Example #8 Binding a ROWID returned by a query
     */
    public function test_oci_bind_by_name_example_8()
    {
        $this->dropTableIfExists('my_table');
        $testConn = $this->getConnection()->getConnection();
        $testConn->exec('CREATE TABLE my_table (id NUMBER, salary NUMBER, name VARCHAR2(40))');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');
        $testConn->exec("INSERT INTO my_table (id, salary, name) VALUES (1, 100, 'Chris')");
        $this->assertTableRowCount('my_table', 1, 'Pre-condition');

        $conn = $this->ociConnect();
        $stmt = $conn->prepare('SELECT ROWID, name FROM my_table WHERE id = :id_bv FOR UPDATE');
        $stmt->bind('id_bv')->toValue(1)->asInt();
        $stmt->execute();
        $row  = $stmt->fetchAssoc();

        /** @var \OCI_Lob $rid */
        $rid  = $row['ROWID'];
        $name = $row['NAME'];

        $stmt->close();

        $this->assertInstanceOf('OCI-Lob', $rid);
        $this->assertEquals('Chris', $name);

        $stmt = $conn->prepare('UPDATE my_table SET name = :n_bv WHERE ROWID = :r_bv');
        $stmt->bind('n_bv')->toValue('CHRIS')->asString();
        $stmt->bind('r_bv')->toVar($rid)->asRowId();
        $stmt->execute();

        $stmt->close();

        $stmt = $conn->prepare('SELECT name FROM my_table');
        $stmt->execute();
        $name = $stmt->fetchColumn();

        $this->assertSame('CHRIS', $name);

        $stmt->close();

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #9 Binding a ROWID on INSERT
     */
    public function test_oci_bind_by_name_example_9()
    {
        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (id NUMBER, salary NUMBER, name VARCHAR2(40))');
        $this->assertTableRowCount('my_table', 0, 'Pre-condition');

        $conn   = $this->ociConnect();
        $rowId  = new OciRowId($conn);
        $inStmt = $conn->prepare('INSERT INTO my_table (id, name) VALUES(:id_bv, :name_bv) RETURNING ROWID INTO :rid');
        $inStmt->bind('id_bv')->toVar($id)->asInt(10);
        $inStmt->bind('name_bv')->toVar($name)->asString(32);
        $inStmt->bind('rid')->toVar($rowId)->asRowId();

        $upStmt = $conn->prepare('UPDATE my_table SET salary = :salary WHERE ROWID = :rid');
        $upStmt->bind('salary')->toVar($salary)->asInt(32);
        $upStmt->bind('rid')->toVar($rowId)->asRowId();

        $data = array(
            1111 => "Larry",
            2222 => "Bill",
            3333 => "Jim"
        );

        // Salary of each person
        $salary = 10000;

        // Insert and immediately update each row
        foreach ($data as $id => $name) {
            $inStmt->execute();
            $upStmt->execute();
        }

        /** @var \Develpup\Oci\OciDescriptor $rowId */
        $rowId->close();
        $inStmt->close();
        $upStmt->close();

        $stmt = $conn->query('SELECT * FROM my_table');
        $rows = $stmt->fetchAll();

        $expected = array(
            array(
                'ID'     => '1111',
                'SALARY' => '10000',
                'NAME'   => 'Larry',
            ),
            array(
                'ID'     => '2222',
                'SALARY' => '10000',
                'NAME'   => 'Bill',
            ),
            array(
                'ID'     => '3333',
                'SALARY' => '10000',
                'NAME'   => 'Jim',
            ),
        );

        $this->assertSame($expected, $rows);

        $stmt->close();

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #10 Binding for a PL/SQL stored function
     */
    public function test_oci_bind_by_name_example_10()
    {
        $this->dropFunctionIfExists('times_three');
        $this->getConnection()->getConnection()->exec(
            'CREATE OR REPLACE FUNCTION times_three(n IN NUMBER) RETURN NUMBER AS BEGIN RETURN n * 3; END;'
        );

        $conn = $this->ociConnect();
        $stmt = $conn->prepare('BEGIN :result := times_three(:num); END;');
        $stmt->bind('num')->toValue(8)->asInt();
        $stmt->bind('result')->toVar($result)->asInt(40);
        $stmt->execute();

        $this->assertSame(24, $result);

        $stmt->close();

        $this->dropFunctionIfExists('times_three');
    }

    /**
     * Example #11 Binding parameters for a PL/SQL stored procedure
     */
    public function test_oci_bind_by_name_example_11()
    {
        $this->dropProcedureIfExists('times_two');
        $this->getConnection()->getConnection()->exec(
            'CREATE OR REPLACE PROCEDURE times_two(p1 IN NUMBER, p2 OUT NUMBER) AS BEGIN p2 := p1 * 2; END;'
        );

        $conn = $this->ociConnect();
        $stmt = $conn->prepare('BEGIN times_two(:p1, :p2); END;');
        $stmt->bind('p1')->toValue(8)->asInt();
        $stmt->bind('p2')->toVar($result)->asInt(40);
        $stmt->execute();

        $this->assertSame(16, $result);

        $stmt->close();

        $this->dropProcedureIfExists('times_two');
    }

    /**
     * Example #12 Binding a CLOB column
     */
    public function test_oci_bind_by_name_example_12()
    {
        $this->dropTableIfExists('my_table');
        $this->getConnection()->getConnection()->exec('CREATE TABLE my_table (my_key NUMBER, my_clob CLOB)');

        $conn = $this->ociConnect();
        $conn->beginTransaction();
        $stmt = $conn->prepare(
            'INSERT INTO my_table (my_key, my_clob) VALUES (:my_key, EMPTY_CLOB()) RETURNING my_clob INTO :my_clob'
        );
        $stmt->bind(':my_key')->toValue($myKey = 12343)->asInt(); // arbitrary key for this example
        $stmt->bind(':my_clob')->toValue('A very long string')->asClob();
        $stmt->execute();

        // $clob->save('A very long string');

        $conn->commit();

        $stmt = $conn->prepare('SELECT my_clob FROM my_table WHERE my_key = :my_key');
        $stmt->bind('my_key')->toValue($myKey)->asInt();
        $stmt->execute();

        $rows = array();

        while (($row = $stmt->fetchAssoc())) {
            $rows[] = $row;
            // In a loop, freeing the large variable before the 2nd fetch reduces PHP's peak memory usage
            unset($row);
        }

        $this->assertSame(array(array('MY_CLOB' => 'A very long string')), $rows);

        $this->dropTableIfExists('my_table');
    }

    /**
     * Example #13 Binding a PL/SQL BOOLEAN
     *
     * @TODO Not working. Could be a version problem.
     */
    public function ___test_oci_bind_by_name_example_13()
    {
        $conn = oci_connect('hr', 'ROOT4oracle', 'localhost/XE');
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message']), E_USER_ERROR);
        }

        $sql = 'BEGIN :output1 := true; :output2 := false; END;';

        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':output1', $output1, -1, OCI_B_BOL);
        oci_bind_by_name($stmt, ':output2', $output2, -1, OCI_B_BOL);
        oci_execute($stmt);

        $this->assertSame(true, $output1);
        $this->assertSame(false, $output2);
    }


    /******************************************************************************************************************\
     * oci_bind_array_by_name()
     *
     * @see http://us.php.net/manual/en/function.oci-bind-array-by-name.php
    \******************************************************************************************************************/

    /**
     * Example #1 oci_bind_array_by_name() example
     */
    public function test_oci_bind_array_by_name_example_1()
    {
        $this->markTestIncomplete('@TODO implement');
    }


    /******************************************************************************************************************\
     * oci_new_cursor()
     *
     * @see http://us.php.net/manual/en/function.oci-new-cursor.php
    \******************************************************************************************************************/

    /**
     * Example #1 Binding a REF CURSOR in an Oracle stored procedure call
     */
    public function test_oci_new_cursor_example_1()
    {
        $this->dropProcedureIfExists('get_employees');
        $this->getConnection()->getConnection()->exec(
            'CREATE OR REPLACE PROCEDURE get_employees (
                my_rc OUT sys_refcursor
            ) AS BEGIN
                OPEN my_rc FOR SELECT first_name FROM employees;
            END;'
        );

        $conn = $this->ociConnect();
        $stmt = $conn->prepare('BEGIN get_employees(:curs); END;');
        $stmt->bind('curs')->toVar($curs)->asCursor();

        /** @var \Develpup\Oci\OciCursor $curs */
        $stmt->execute();
        $curs->execute();

        $names = array();
        while (($row = $curs->fetchAssoc())) {
            $names[] = $row['FIRST_NAME'];
        }

        $this->assertSame($this->getEmployeeFirstNames(), $names);

        $stmt->close();
        $curs->close();

        $this->dropProcedureIfExists('get_employees');
    }


    /******************************************************************************************************************\
     * oci_new_descriptor()
     *
     * @see http://us.php.net/manual/en/function.oci-new-descriptor.php
    \******************************************************************************************************************/

//    public function test_oci_new_descriptor_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_new_descriptor_example_2()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_num_rows()
     *
     * @see http://us.php.net/manual/en/function.oci-num-rows.php
    \******************************************************************************************************************/

//    public function test_oci_num_rows_example_1()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_num_fields()
     *
     * @see http://us.php.net/manual/en/function.oci-num-fields.php
    \******************************************************************************************************************/

//    public function test_oci_num_fields_example_1()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_fetch_array()
     *
     * @see http://us.php.net/manual/en/function.oci-fetch-array.php
    \******************************************************************************************************************/

//    public function test_oci_fetch_array_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_3()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_4()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_5()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_6()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_7()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_8()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_9()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_10()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_array_example_11()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_fetch_all()
     *
     * @see http://us.php.net/manual/en/function.oci-fetch-all.php
    \******************************************************************************************************************/

//    public function test_oci_fetch_all_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_all_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_all_example_3()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_fetch_object()
     *
     * @see http://us.php.net/manual/en/function.oci-fetch-object.php
    \******************************************************************************************************************/

//    public function test_oci_fetch_object_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_object_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_fetch_object_example_3()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_commit()
     *
     * @see http://us.php.net/manual/en/function.oci-commit.php
    \******************************************************************************************************************/

//    public function test_oci_commit_example_1()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_rollback()
     *
     * @see http://us.php.net/manual/en/function.oci-rollback.php
    \******************************************************************************************************************/

//    public function test_oci_rollback_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_rollback_example_2()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_error()
     *
     * @see http://us.php.net/manual/en/function.oci-error.php
    \******************************************************************************************************************/

//    public function test_oci_error_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_error_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_error_example_3()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_close()
     *
     * @see http://us.php.net/manual/en/function.oci-close.php
    \******************************************************************************************************************/

//    public function test_oci_close_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_close_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_close_example_3()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_close_example_4()
//    {
//        $this->markTestSkipped();
//    }

    /**
     * @TODO I know there is a way to get this from the XML DataSet, but no time right now.
     *
     * @return array
     */
    protected function getEmployeeFirstNames()
    {
        return array (
            0 => 'Ellen',
            1 => 'Sundar',
            2 => 'Mozhe',
            3 => 'David',
            4 => 'Hermann',
            5 => 'Shelli',
            6 => 'Amit',
            7 => 'Elizabeth',
            8 => 'Sarah',
            9 => 'David',
            10 => 'Laura',
            11 => 'Harrison',
            12 => 'Alexis',
            13 => 'Anthony',
            14 => 'Gerald',
            15 => 'Nanette',
            16 => 'John',
            17 => 'Kelly',
            18 => 'Karen',
            19 => 'Curtis',
            20 => 'Lex',
            21 => 'Julia',
            22 => 'Jennifer',
            23 => 'Louise',
            24 => 'Bruce',
            25 => 'Alberto',
            26 => 'Britney',
            27 => 'Daniel',
            28 => 'Pat',
            29 => 'Kevin',
            30 => 'Jean',
            31 => 'Tayler',
            32 => 'Adam',
            33 => 'Timothy',
            34 => 'Ki',
            35 => 'Girard',
            36 => 'William',
            37 => 'Douglas',
            38 => 'Kimberely',
            39 => 'Nancy',
            40 => 'Danielle',
            41 => 'Peter',
            42 => 'Michael',
            43 => 'Shelley',
            44 => 'Guy',
            45 => 'Alexander',
            46 => 'Alyssa',
            47 => 'Charles',
            48 => 'Vance',
            49 => 'Payam',
            50 => 'Alexander',
            51 => 'Janette',
            52 => 'Steven',
            53 => 'Neena',
            54 => 'Sundita',
            55 => 'Renske',
            56 => 'James',
            57 => 'David',
            58 => 'Jack',
            59 => 'Diana',
            60 => 'Jason',
            61 => 'Steven',
            62 => 'James',
            63 => 'Mattea',
            64 => 'Randall',
            65 => 'Susan',
            66 => 'Samuel',
            67 => 'Allan',
            68 => 'Irene',
            69 => 'Kevin',
            70 => 'Julia',
            71 => 'Donald',
            72 => 'Christopher',
            73 => 'TJ',
            74 => 'Lisa',
            75 => 'Karen',
            76 => 'Valli',
            77 => 'Joshua',
            78 => 'Randall',
            79 => 'Hazel',
            80 => 'Luis',
            81 => 'Trenna',
            82 => 'Den',
            83 => 'Michael',
            84 => 'John',
            85 => 'Nandita',
            86 => 'Ismael',
            87 => 'John',
            88 => 'Sarath',
            89 => 'Lindsey',
            90 => 'William',
            91 => 'Stephen',
            92 => 'Martha',
            93 => 'Patrick',
            94 => 'Jonathon',
            95 => 'Winston',
            96 => 'Sigal',
            97 => 'Peter',
            98 => 'Oliver',
            99 => 'Jose Manuel',
            100 => 'Peter',
            101 => 'Clara',
            102 => 'Shanta',
            103 => 'Alana',
            104 => 'Matthew',
            105 => 'Jennifer',
            106 => 'Eleni',
        );
    }
}
