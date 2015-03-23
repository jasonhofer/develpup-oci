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
 * Class AbstractOciResource
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 5:47 PM
 */
abstract class AbstractOciResource
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @return bool
     */
    abstract public function close();

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @throws OciException
     */
    protected function assertValidResource()
    {
        if (!$this->resource) {
            throw OciException::fromErrorInfo($this->errorInfo());
        }
    }

    /**
     * @return array|false
     */
    public function errorInfo()
    {
        return ($this->resource ? oci_error($this->resource) : oci_error());
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}
