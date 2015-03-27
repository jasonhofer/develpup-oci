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
 * Class OciRowId
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-27 1:18 AM
 */
class OciRowId extends OciDescriptor
{
    /**
     * @param OciConnection $connection
     * @param \OCI_Lob|null $lob
     */
    public function __construct(OciConnection $connection, $lob = null)
    {
        parent::__construct($connection, self::TYPE_ROW_ID, $lob);
    }
}
