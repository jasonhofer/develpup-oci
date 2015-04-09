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
 * Class MyReadMeExamplesTest
 *
 * @package Develpup\Test\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-09 12:21 PM
 */
class MyReadMeExamplesTest extends AbstractFunctionalTestCase
{
    /**
     * @group readme
     */
    public function testMyReadMeExamples()
    {
        /** @var \Develpup\Oci\OciCursor $cursor *///phpunit//
        /** @var \Develpup\Oci\OciLob $clob *///phpunit//
        $this->dropTableIfExists('my_table');//phpunit//
        $this->dropProcedureIfExists('get_numbers');//phpunit//
        $conn = $this->ociConnect();//phpunit//

        // Connect to the database.
        //example//$conn = new \Develpup\Oci\OciConnection('hr', 'welcome', 'localhost/XE');

        // Execute SQL statement.
        $conn->exec('CREATE TABLE my_table (my_number NUMBER, my_clob CLOB)');

        // Prepare SQL statement for execution and return an OciStatement object.
        $stmt = $conn->prepare(
            'INSERT INTO my_table (my_number, my_clob)
             VALUES (:int_param, EMPTY_CLOB())
             RETURNING my_clob INTO :clob_param'
        );

        // Must execute within a transaction so that $clob->save() will work.
        $conn->beginTransaction();

        // We can bind the parameters by value:
        $stmt->bind('int_param')->toValue(1)->asInt();
        $stmt->bind('clob_param')->toValue('CLOB #1')->asClob();
        $stmt->execute();

        // We can bind the parameters by reference:
        $stmt->bind('int_param')->toVar($num); // No need to call as{Type}() more than once.
        $stmt->bind('clob_param')->toVar($clob);
        for ($num = 2; $num <= 4; ++$num) {
            $stmt->execute();
            $clob->save("CLOB #{$num}");
        }

        // And then we can bind them by value again:
        $stmt->bind('int_param')->toValue(5);
        $stmt->bind('clob_param')->toValue('CLOB #5');
        $stmt->execute();

        // Commit the changes and free the clob
        $conn->commit();
        $clob->close();

        // Execute SQL statement, returning a result set as an OciStatement object.
        $stmt   = $conn->query('SELECT * FROM my_table');
        $values = array();
        // Fetch each row from the result set as an associative array.
        while (($row = $stmt->fetchAssoc())) {
            $values[ (int) $row['MY_NUMBER'] ] = $row['MY_CLOB'];
        }
        //example//assert($values === array(1 => 'CLOB #1', 2 => 'CLOB #2', 3 => 'CLOB #3', 4 => 'CLOB #4', 5 => 'CLOB #5'));
        $this->assertSame(array(1 => 'CLOB #1', 2 => 'CLOB #2', 3 => 'CLOB #3', 4 => 'CLOB #4', 5 => 'CLOB #5'), $values);//phpunit//

        // Fetch all values from a single column.
        $stmt = $conn->prepare('SELECT my_number FROM my_table WHERE my_number > :my_param');
        $stmt->bind('my_param')->toValue(2)->asInt();
        $stmt->execute();
        $values = $stmt->fetchAllColumn();
        //example//assert($values === array('3', '4', '5'));
        $this->assertSame(array('3', '4', '5'), $values);//phpunit//

        // Create and execute a stored procedure that defines a cursor output parameter.
        $conn->exec(
            'CREATE PROCEDURE get_numbers (my_rc OUT sys_refcursor) AS BEGIN
                OPEN my_rc FOR SELECT my_number FROM my_table;
            END;'
        );
        $stmt = $conn->prepare('BEGIN get_numbers(:my_cursor); END;');
        $stmt->bind('my_cursor')->toVar($cursor)->asCursor();
        $stmt->execute(); // execute statement first
        $cursor->execute();
        $values = $cursor->fetchAllColumn();
        //example//assert($values === array('1', '2', '3', '4', '5'));
        $this->assertSame(array('1', '2', '3', '4', '5'), $values);//phpunit//

        //example//$cursor->close();
        //example//$stmt->close();
        //example//$conn->close();

        $this->dropTableIfExists('my_table');//phpunit//
        $this->dropProcedureIfExists('get_numbers');//phpunit//
    }
}
