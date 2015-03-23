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
 * Class OciStatement
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 7:25 PM
 */
class OciStatement extends OciCursor
{
    /**
     * @var OciParameter[]
     */
    protected $paramMap = array();

    /**
     * @var bool
     */
    private $bound = false;

    /**
     * @param OciConnection $connection
     * @param string        $statement
     */
    public function __construct(OciConnection $connection, $statement)
    {
        $this->connection = $connection;
        $this->resource   = oci_parse($connection->getResource(), $statement);

        $this->assertValidResource();
    }

    public function bind($name)
    {
        if (!isset($this->paramMap[$name])) {
            return $this->paramMap[$name] = new OciParameter($this, $name);
        }

        return $this->paramMap[$name];
    }

    /**
     * @return bool
     *
     * @throws OciException
     */
    public function execute()
    {
        if (!$this->bound) {
            foreach ($this->paramMap as $param) {
                if (!$param->bind()) {
                    throw OciException::fromErrorInfo($this->errorInfo());
                }
            }
            $this->bound = true;
        }

        return parent::execute();
    }

    /**
     * @return bool
     */
    public function close()
    {
        return ($this->resource ? oci_free_statement($this->resource) : true);
    }
}
