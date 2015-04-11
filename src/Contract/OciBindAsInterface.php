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
 * Interface OciBindAsInterface
 *
 * @package Develpup\Oci\Contract
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-11 12:07 AM
 */
interface OciBindAsInterface
{
    /**
     * @param int $size
     */
    public function asString($size = -1);

    /**
     * @param int $size
     */
    public function asInt($size = -1);

    /**
     *
     */
    public function asBool();

    /**
     * @param int $size
     */
    public function asLong($size = -1);

    /**
     * @param int $size
     */
    public function asClob($size = -1);

    /**
     * @param int $size
     */
    public function asBlob($size = -1);

    /**
     *
     */
    public function asCursor();

    /**
     *
     */
    public function asRowId();
}