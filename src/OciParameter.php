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

    protected $name;
    protected $value = null;
    protected $type;
    protected $length = -1;
    protected $output = false;

    /**
     * @param OciStatement $statement
     */
    public function __construct(OciStatement $statement, $name)
    {
        $this->statement = $statement;
        $this->name      = $name;
    }

    public function toVal($val)
    {
        $this->value = $val;

        return $this;
    }

    public function toVar(&$var)
    {
        $this->value = $var;

        return $this;
    }

    public function toOutVar(&$var)
    {
        $this->value  = $var;
        $this->output = true;

        return $this;
    }

    public function asString()
    {
        $this->setType(SQLT_CHR);

        return $this;
    }

    public function asInt()
    {
        $this->setType(OCI_B_INT);

        return $this;
    }

    public function asLong()
    {
        $this->setType(SQLT_LNG);

        return $this;
    }

    public function asClob()
    {
        $this->setType(OCI_B_CLOB);

        return $this;
    }

    public function asBlob()
    {
        $this->setType(OCI_B_BLOB);

        return $this;
    }

    public function withLength($length)
    {
        $this->length = (int) $length;

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
            throw new OciException('Cannot change type of bound parameter.');
        }

        $this->type = $type;
    }

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
            $this->length,
            $this->type
        );
    }

    /**
     * @return bool
     */
    protected function doBind()
    {
        return oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $this->value,
            $this->length,
            $this->type
        );
    }
}
