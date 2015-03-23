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
 * Class OciParameter
 *
 * OciStatement::bind() returns an OciParameter:
 * <code>
 * $stmt->bind('name')->toVal('John')->asString();
 * $stmt->bind('age')->toVal(42)->asInt();
 * $stmt->bind('new_id')->toOutVar($newId)->asInt();
 * $stmt->bind('results')->toVar($results)->asCursor();
 * </code>
 *
 * @package Develpup\Oci
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2015-03-22 7:40 PM
 */
class OciParameter
{
    /**
     * @var OciStatement
     */
    protected $statement;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var int
     */
    protected $maxLength = -1;

    /**
     * @var bool
     */
    protected $output = false;

    /**
     * @param OciStatement $statement
     * @param string       $name
     */
    public function __construct(OciStatement $statement, $name)
    {
        $this->statement = $statement;
        $this->name      = (string) $name;
    }

    /**
     * @param mixed $val
     *
     * @return $this
     */
    public function toVal($val)
    {
        $this->value = $val;

        return $this;
    }

    /**
     * @param mixed &$var
     *
     * @return $this
     */
    public function toVar(&$var)
    {
        $this->value = &$var;

        return $this;
    }

    /**
     * @param null &$var
     * @param int  $maxLength
     *
     * @return $this
     */
    public function toOutVar(&$var, $maxLength = -1)
    {
        $this->value     = &$var;
        $this->maxLength = (int) $maxLength;
        $this->output    = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function asString()
    {
        $this->setType(SQLT_CHR);

        return $this;
    }

    /**
     * @return $this
     */
    public function asInt()
    {
        $this->setType(OCI_B_INT);

        return $this;
    }

    /**
     * @return $this
     */
    public function asLong()
    {
        $this->setType(SQLT_LNG);

        return $this;
    }

    /**
     * @return $this
     */
    public function asClob()
    {
        $this->setType(OCI_B_CLOB);

        return $this;
    }

    /**
     * @return $this
     */
    public function asBlob()
    {
        $this->setType(OCI_B_BLOB);

        return $this;
    }

    /**
     * @return $this
     */
    public function asCursor()
    {
        $this->value     = new OciCursor($this->statement->getConnection());
        $this->type      = OCI_B_CURSOR;
        $this->maxLength = -1;

        return $this;
    }

    /**
     * @param int $maxLength
     *
     * @return $this
     *
     * @throws OciException
     */
    public function withMaxLength($maxLength)
    {
        if (is_int($this->type)) {
            throw new OciException('Cannot change parameter type after it has been defined.');
        }

        $this->maxLength = (int) $maxLength;

        return $this;
    }

    /**
     * @param int $type
     *
     * @throws OciException
     */
    protected function setType($type)
    {
        if (is_int($this->type)) {
            throw new OciException('Cannot change parameter type after it has been defined.');
        }

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function bind()
    {
        static $lobTypes = array(OCI_B_CLOB, OCI_B_BLOB);

        if (in_array($this->type, $lobTypes)) {
            return $this->doBindLob();
        }

        return $this->doBind();
    }

    /**
     * @return bool
     */
    protected function doBindLob()
    {
        $locator = new OciDescriptor($this->statement->getConnection(), OciDescriptor::TYPE_LOB);

        /*
        $this->statement->afterExecute(
            function () use ($locator) {
                $locator->save($this->variable);
            }
        );
        */

        return oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $locator->getResource(),
            $this->maxLength,
            $this->type
        );
    }

    /**
     * @return bool
     */
    protected function doBind()
    {
        if ($this->value instanceof OciCursor) {
            $value = $this->value->getResource();
        } else {
            $value = &$this->value;
        }

        return oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $value,
            $this->maxLength,
            $this->type
        );
    }
}
