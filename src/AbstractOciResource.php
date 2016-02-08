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

// Only defined in OCI8 2.0.7 and Oracle Database 12c.
// See: http://php.net/manual/en/function.oci-bind-by-name.php
defined('OCI_B_BOL') or define('OCI_B_BOL', 252);
defined('SQLT_BOL') or define('SQLT_BOL', OCI_B_BOL);

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
     * @return bool
     */
    protected function checkValidResource()
    {
        return is_resource($this->resource);
    }

    /**
     * @throws OciException
     */
    protected function assertValidResource()
    {
        if (!$this->checkValidResource()) {
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
