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
 * Class OciLob
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-27 1:22 AM
 */
class OciLob extends OciDescriptor
{
    /**
     * @param OciConnection $connection
     */
    public function __construct(OciConnection $connection)
    {
        parent::__construct($connection, self::TYPE_LOB);
    }
}
