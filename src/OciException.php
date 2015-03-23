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
 * Class OciException
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 4:22 PM
 */
class OciException extends \Exception
{
    /**
     * @param array $error
     *
     * @return OciException
     */
    public static function fromErrorInfo($error)
    {
        if (is_array($error)) {
            return new self($error['message'], $error['code']);
        } else {
            return new self('General OCI failure.');
        }
    }
}
