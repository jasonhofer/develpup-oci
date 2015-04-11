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
 * Interface OciStatementVisitorInterface
 *
 * @package Develpup\Oci\Contract
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-04-10 11:57 PM
 */
interface OciStatementVisitorInterface
{
    /**
     * @param resource                     $sth
     * @param string                       $sql
     * @param \Develpup\Oci\OciParameter[] $params
     */
    public function visitStatement($sth, $sql, array $params);
}
