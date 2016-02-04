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
 * Class OciStatement
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 7:25 PM
 */
class OciStatement extends OciCursor
{
    /**
     * @var string
     */
    protected $sql;

    /**
     * @var OciParameter[]
     */
    protected $paramMap = array();

    /**
     * @var callable[]
     */
    private $preExecute = array();

    /**
     * @var callable[]
     */
    private $postExecute = array();

    /**
     * @param OciConnection $connection
     * @param string        $sql
     */
    public function __construct(OciConnection $connection, $sql)
    {
        $this->connection = $connection;
        $this->sql        = $sql;
        $this->resource   = oci_parse($connection->getResource(), $sql);

        $this->assertValidResource();
    }

    /**
     * @param string $name
     *
     * @return Contract\OciBindToInterface
     */
    public function bind($name)
    {
        if (!isset($this->paramMap[$name])) {
            return $this->paramMap[$name] = new OciParameter($this, $name);
        }

        return $this->paramMap[$name];
    }

    /**
     * @return bool
     *
     * @throws OciException
     */
    public function execute()
    {
        foreach ($this->preExecute as $callback) {
            call_user_func($callback, $this);
        }

        foreach ($this->paramMap as $name => $param) {
            if (!$param->bind()) {
                throw OciException::failedToBindParameter($this->errorInfo(), $name);
            }
        }

        $result = parent::execute();

        foreach ($this->postExecute as $callback) {
            call_user_func($callback, $this, $result);
        }

        return $result;
    }

    /**
     * @return bool
     *
     * @throws OciException
     */
    public function close()
    {
        if (null === $this->resource) {
            return true;
        }

        $closed         = oci_free_statement($this->resource);
        $this->resource = null;

        return $closed;
    }

    /**
     * @param string   $name
     * @param callable $callback
     */
    public function onPreExecute($name, $callback)
    {
        $this->preExecute[$name] = $callback;
    }

    /**
     * @param string $name
     */
    public function offPreExecute($name)
    {
        unset($this->preExecute[$name]);
    }

    /**
     * @param string   $name
     * @param callable $callback
     */
    public function onPostExecute($name, $callback)
    {
        $this->postExecute[$name] = $callback;
    }

    /**
     * @param string $name
     */
    public function offPostExecute($name)
    {
        unset($this->postExecute[$name]);
    }

    /**
     * @param Contract\OciStatementVisitorInterface $visitor
     */
    public function accept(Contract\OciStatementVisitorInterface $visitor)
    {
        $visitor->visitStatement($this->resource, $this->sql, $this->paramMap);
    }
}
