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
     * @param int $maxSize
     *
     * @return OciAllowNullInterface
     */
    public function asString($maxSize = -1);

    /**
     * @param int $maxSize
     *
     * @return OciAllowNullInterface
     */
    public function asInt($maxSize = -1);

    /**
     * @return OciAllowNullInterface
     */
    public function asBool();

    /**
     * @param int $maxSize
     *
     * @return OciAllowNullInterface
     */
    public function asClob($maxSize = -1);

    /**
     * @param int $maxSize
     *
     * @return OciAllowNullInterface
     */
    public function asBlob($maxSize = -1);

    /**
     *
     */
    public function asCursor();

    /**
     *
     */
    public function asRowId();

    /**
     * @return OciBindAsArrayInterface
     */
    public function asArray();
}
