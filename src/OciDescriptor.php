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
    const TYPE_LOB    = OCI_D_LOB;
    const TYPE_FILE   = OCI_D_FILE;
    const TYPE_ROW_ID = OCI_D_ROWID;

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
     * @param \OCI_Lob      $lob
     */
    public function __construct(OciConnection $connection, $type = self::TYPE_LOB, $lob = null)
    {
        $this->connection = $connection;
        $this->type       = $type;

        if (null === $lob) {
            $this->resource = oci_new_descriptor($connection->getResource(), $type);
        } else {
            $this->resource = $lob;
        }

        $this->assertValidResource();
    }

    /**
     * @param string $data
     * @param int    $offset
     *
     * @return bool
     */
    public function save($data, $offset = null)
    {
        return $this->resource->save($data, $offset);
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
    protected function checkValidResource()
    {
        static $lobClass = 'OCI-Lob';

        return $this->resource instanceof $lobClass;
    }

    /**
     * @return bool
     */
    public function close()
    {
        if (null === $this->resource) {
            return true;
        }

        $closed         = $this->resource->free();
        $this->resource = null;

        return $closed;
    }
}
