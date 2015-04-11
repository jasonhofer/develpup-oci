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
 * Interface OciBindToInterface
 *
 * @package Develpup\Oci\Contract
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-11 12:04 AM
 */
interface OciBindToInterface
{
    /**
     * @param mixed $val
     *
     * @return OciBindAsInterface
     */
    public function toValue($val);

    /**
     * @param mixed &$var
     *
     * @return OciBindAsInterface
     */
    public function toVar(&$var);
}
