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
//    public function test_oci_parse_example_1()
//    {
//    }

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
        $stmt->bind('p2')->toOutVar($p2, 40)->asInt();

        $stmt->execute();

        $this->assertSame(16, $p2, 'Output variable did not contain the expected value.');

        $stmt->close();
        $conn->close();

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
//    public function test_oci_execute_example_1()
//    {
//    }

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
//    public function test_oci_execute_example_4()
//    {
//    }

    /**
     * Example #5 oci_execute() with OCI_DESCRIBE_ONLY example
     *
     * @group new
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

//    public function test_oci_bind_by_name_example_1()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_2()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_3()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_4()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_5()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_6()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_7()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_8()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_9()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_10()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_11()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_12()
//    {
//        $this->markTestSkipped();
//    }
//
//    public function test_oci_bind_by_name_example_13()
//    {
//        $this->markTestSkipped();
//    }


    /******************************************************************************************************************\
     * oci_new_cursor()
     *
     * @see http://us.php.net/manual/en/function.oci-new-cursor.php
    \******************************************************************************************************************/

//    public function test_oci_new_cursor_example_1()
//    {
//        $this->markTestSkipped();
//    }


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
     * oci_fetch_assoc()
     *
     * @see http://us.php.net/manual/en/function.oci-fetch-assoc.php
    \******************************************************************************************************************/

//    public function test_oci_fetch_assoc_example_1()
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
}
