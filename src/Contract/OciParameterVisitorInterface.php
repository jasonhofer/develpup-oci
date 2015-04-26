<?php

/*
 * This file is part of the Develpup OCI package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Develpup\Oci\Contract;

/**
 * Interface OciParameterVisitorInterface
 *
 * @package Develpup\Oci\Contract
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-11 12:17 AM
 */
interface OciParameterVisitorInterface
{
    /**
     * @param string $name
     * @param bool   $byReference
     * @param mixed  $variable
     * @param mixed  $value
     * @param int    $type
     * @param bool   $bindAsArray
     */
    public function visitParameter($name, $byReference, &$variable, $value, $type, $bindAsArray);
}
