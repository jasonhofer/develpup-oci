Develpup OCI
============

This is still under development, so don't get your hopes up. It's *loosely* based on the Doctrine
Connection and Statement classes, but does not implement nor extend any Doctrine interfaces or
classes.

**Use at your own risk.**

### Examples
```php
// Connect to the database.
$conn = new \Develpup\Oci\OciConnection('hr', 'welcome', 'localhost/XE');

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
assert($values === array(1 => 'CLOB #1', 2 => 'CLOB #2', 3 => 'CLOB #3', 4 => 'CLOB #4', 5 => 'CLOB #5'));

// Fetch all values from a single column.
$stmt = $conn->prepare('SELECT my_number FROM my_table WHERE my_number > :my_param');
$stmt->bind('my_param')->toValue(2)->asInt();
$stmt->execute();
$values = $stmt->fetchAllColumn();
assert($values === array('3', '4', '5'));

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
assert($values === array('1', '2', '3', '4', '5'));

$cursor->close();
$stmt->close();
$conn->close();
```

For many other examples, see [`tests/functional/PhpManualOciExamplesTest.php`][1].
Each test tries to reproduce an example from the [OCI8 extension pages of the PHP manual][2].

[1]: https://github.com/jasonhofer/develpup-oci/blob/master/tests/functional/PhpManualOciExamplesTest.php
[2]: http://php.net/manual/book.oci8.php
