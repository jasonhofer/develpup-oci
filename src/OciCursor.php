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
     * @param resource      $resource
     */
    public function __construct(OciConnection $connection, $resource = null)
    {
        $this->connection = $connection;
        $this->resource   = ($resource ? $resource : oci_new_cursor($connection->getResource()));

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

        if (false === $ret) {
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

        if (false === oci_fetch_all($this->resource, $results, $skip, $maxRows, $flags)) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }

        return $results;
    }

    /**
     * @param int $columnIndex
     *
     * @return mixed
     */
    public function fetchColumn($columnIndex = 0)
    {
        $row = $this->fetchArray();

        if (false === $row) {
            return false;
        }

        return (isset($row[$columnIndex]) ? $row[$columnIndex] : null);
    }

    /**
     * @param int $columnIndex
     *
     * @return array
     */
    public function fetchAllColumn($columnIndex = 0)
    {
        $values = array();

        while (false !== ($value = $this->fetchColumn($columnIndex))) {
            $values[] = $value;
            unset($value); // @TODO look into whether this really does keep PHP peak memory usage down.
        }

        return $values;
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
     * @param int $position 1-based position of column.
     *
     * @return string|false
     */
    public function columnName($position)
    {
        return oci_field_name($this->resource, (int) $position);
    }

    /**
     * @param int $position 1-based position of column.
     *
     * @return string|false
     */
    public function columnType($position)
    {
        return oci_field_type($this->resource, (int) $position);
    }

    /**
     * @param int $rows
     *
     * @return $this
     *
     * @throws OciException
     */
    public function setPrefetch($rows)
    {
        if (false === oci_set_prefetch($this->resource, (int) $rows)) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }

        return $this;
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
