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
     * @param array|false $error
     *
     * @return OciException
     */
    public static function fromErrorInfo($error)
    {
        list($message, $code) = self::getErrorInfo($error);

        return new self($message, $code);
    }

    /**
     * Make the error "oci_bind_by_name(): ORA-01036: illegal variable name/number" error a lot easier to debug.
     *
     * @param array|false $error
     * @param string      $parameter
     *
     * @return OciException
     */
    public static function failedToBindParameter($error, $parameter)
    {
        list($message, $code) = self::getErrorInfo($error);

        return new self($message . ': "' . $parameter . '"', $code);
    }

    /**
     * @param array|false $error
     *
     * @return array
     */
    private static function getErrorInfo($error)
    {
        if (is_array($error)) {
            return array($error['message'], $error['code']);
        } else {
            return array('Unknown OCI failure.', null);
        }
    }
}
