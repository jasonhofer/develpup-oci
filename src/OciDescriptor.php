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
 * Class OciDescriptor
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 8:15 PM
 */
class OciDescriptor extends AbstractOciResource
{
    const TYPE_LOB    = OCI_DTYPE_LOB;
    const TYPE_FILE   = OCI_DTYPE_FILE;
    const TYPE_ROW_ID = OCI_DTYPE_ROWID;

    /**
     * @var \OCI_Lob
     */
    protected $resource;

    /**
     * @var OciConnection
     */
    protected $connection;

    /**
     * @var int
     */
    protected $type;

    /**
     * @param OciConnection $connection
     * @param int           $type
     */
    public function __construct(OciConnection $connection, $type = self::TYPE_LOB)
    {
        $this->connection = $connection;
        $this->type       = $type;
        $this->resource   = oci_new_descriptor($connection->getResource(), $type);

        $this->assertValidResource();
    }

    /**
     * @return \OCI_Lob
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return $this->resource->free();
    }
}
