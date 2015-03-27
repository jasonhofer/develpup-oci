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
 * Class OciCursor
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 7:30 PM
 */
class OciCursor extends AbstractOciResource
{
    /**
     * @var OciConnection
     */
    protected $connection;

    /**
     * @param OciConnection $connection
     */
    public function __construct(OciConnection $connection)
    {
        $this->connection = $connection;
        $this->resource   = oci_new_cursor($connection->getResource());

        $this->assertValidResource();
    }

    /**
     * @return bool
     *
     * @throws OciException
     */
    public function execute()
    {
        $ret = @oci_execute($this->resource, $this->connection->getExecuteMode());

        if (!$ret) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function fetchAssoc()
    {
        return oci_fetch_array($this->resource, OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS);
    }

    /**
     * @param int $flags
     *
     * @return array
     */
    public function fetchArray($flags = null)
    {
        if (null === $flags) {
            $flags = OCI_NUM | OCI_RETURN_NULLS | OCI_RETURN_LOBS;
        }

        return oci_fetch_array($this->resource, $flags);
    }

    /**
     * @return object
     */
    public function fetchObject()
    {
        return oci_fetch_object($this->resource);
    }

    /**
     * @param int $skip
     * @param int $maxRows
     * @param int $flags
     *
     * @return array
     *
     * @throws OciException
     */
    public function fetchAll($skip = 0, $maxRows = -1, $flags = null)
    {
        if (null === $flags) {
            $flags = OCI_FETCHSTATEMENT_BY_ROW | OCI_ASSOC | OCI_RETURN_NULLS | OCI_RETURN_LOBS;
        }

        $results = array();

        if (!oci_fetch_all($this->resource, $results, $skip, $maxRows, $flags)) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }

        return $results;
    }

    /**
     * @param int $columnIndex
     *
     * @return array|null
     */
    public function fetchColumn($columnIndex = 0)
    {
        $row = $this->fetchArray(OCI_NUM | OCI_RETURN_NULLS | OCI_RETURN_LOBS);

        if (false === $row) {
            return false;
        }

        return (isset($row[$columnIndex]) ? $row[$columnIndex] : null);
    }

    /**
     * @return int
     */
    public function columnCount()
    {
        return oci_num_fields($this->resource);
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return oci_num_rows($this->resource);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function columnName($index)
    {
        return oci_field_name($this->resource, (int) $index);
    }

    /**
     * @return OciConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $this->resource and oci_free_cursor($this->resource);

        $this->resource = null;

        return true;
    }
}
