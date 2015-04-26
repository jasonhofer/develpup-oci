<?php

/*
 * This file is part of the Conduit system.
 *
 * (c) 2015 Halight, Inc.
 */

namespace Develpup\Oci\Contract;

/**
 * Interface OciBindAsArrayInterface
 *
 * @package Develpup\Oci\Contract
 * @author Jason Hofer <jason.hofer@halight.com>
 * 2015-04-26 2:45 PM
 */
interface OciBindAsArrayInterface
{
    /**
     * @param int $maxSize
     */
    public function ofStrings($maxSize = -1);

    /**
     * @param int $maxSize
     */
    public function ofInts($maxSize = -1);
}
