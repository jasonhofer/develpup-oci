<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\Oci;

/**
 * Class OciConnection
 *
 * @package
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 5:53 PM
 */
class OciConnection extends AbstractOciResource
{
    /**
     * @var int
     */
    protected $executeMode = OCI_COMMIT_ON_SUCCESS;

    /**
     * @param string      $username
     * @param string      $password
     * @param string      $dsn
     * @param string|null $charset
     * @param int|null    $sessionMode
     * @param bool        $persistent
     *
     * @throws OciException
     */
    public function __construct($username, $password, $dsn, $charset = null, $sessionMode = null, $persistent = false)
    {
        if (!defined('OCI_NO_AUTO_COMMIT')) {
            define('OCI_NO_AUTO_COMMIT', OCI_DEFAULT);
        }

        $this->resource = $persistent ?
            @oci_pconnect($username, $password, $dsn, $charset, $sessionMode ?: OCI_DEFAULT) :
            @oci_connect($username, $password, $dsn, $charset, $sessionMode ?: OCI_DEFAULT);

        $this->assertValidResource();
    }

    /**
     * @return int
     */
    public function getExecuteMode()
    {
        return $this->executeMode;
    }

    /**
     * @param string $prepareString
     *
     * @return OciStatement
     */
    public function prepare($prepareString)
    {
        return new OciStatement($this, $prepareString);
    }

    /**
     * @param string $sql
     *
     * @return OciStatement
     *
     * @throws OciException
     */
    public function query($sql)
    {
        $stmt = $this->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /**
     * @param OciStatement $statement
     *
     * @return OciStatement
     *
     * @throws OciException
     */
    public function describe(OciStatement $statement)
    {
        $this->executeMode = OCI_DESCRIBE_ONLY;

        $statement->execute();

        $this->executeMode = OCI_COMMIT_ON_SUCCESS;
    }

    /**
     * @param string $statement
     *
     * @return int
     *
     * @throws OciException
     */
    public function exec($statement)
    {
        $stmt = $this->prepare($statement);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param mixed $value
     *
     * @return mixed|string
     */
    public function quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $value = str_replace("'", "''", $value);

        return "'" . addcslashes($value, "\000\n\r\\\032") . "'";
    }

    /**
     * @return $this
     */
    public function beginTransaction()
    {
        $this->executeMode = OCI_NO_AUTO_COMMIT;

        return $this;
    }

    /**
     * @return true
     *
     * @throws OciException
     */
    public function commit()
    {
        if (!oci_commit($this->resource)) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }
        $this->executeMode = OCI_COMMIT_ON_SUCCESS;

        return true;
    }

    /**
     * @returns true
     *
     * @throws OciException
     */
    public function rollBack()
    {
        if (!oci_rollback($this->resource)) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }
        $this->executeMode = OCI_COMMIT_ON_SUCCESS;

        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        if (null === $this->resource) {
            return true;
        }

        $closed         = oci_close($this->resource);
        $this->resource = null;

        return $closed;
    }
}
