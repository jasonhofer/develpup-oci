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
    protected $size = -1;

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
    public function toValue($val)
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
     *
     * @return $this
     */
    public function toOutVar(&$var)
    {
        $this->value  = &$var;
        $this->output = true;

        return $this;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function asString($size = -1)
    {
        $this->setType(SQLT_CHR);
        $this->size = (int) $size;

        return $this;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function asInt($size = -1)
    {
        $this->setType(OCI_B_INT);
        $this->size = (int) $size;

        return $this;
    }

    /**
     * @return $this
     */
    public function asBool()
    {
        $this->setType(OCI_B_BOL);

        return $this;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function asLong($size = -1)
    {
        $this->setType(SQLT_LNG);
        $this->size = (int) $size;

        return $this;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function asClob($size = -1)
    {
        $this->setType(OCI_B_CLOB);
        $this->size = (int) $size;

        return $this;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function asBlob($size = -1)
    {
        $this->setType(OCI_B_BLOB);
        $this->size = (int) $size;

        return $this;
    }

    /**
     * @return $this
     */
    public function asCursor()
    {
        $this->setType(OCI_B_CURSOR);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws OciException
     */
    public function asRowId()
    {
        $this->setType(OCI_B_ROWID);

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
        static $lobClass = 'OCI-Lob';

        switch ($this->type) {
            case OCI_B_CURSOR:
                $this->value = new OciCursor($this->statement->getConnection());
                $this->size  = -1;
                $cursor      = $this->value->getResource();

                return $this->bindTo($cursor);

            case OCI_B_ROWID:
                if (!$this->value instanceof OciRowId) {
                    $this->value = new OciRowId(
                        $this->statement->getConnection(),
                        ($this->value instanceof $lobClass ? $this->value : null)
                    );
                }
                $this->size = -1;
                $descriptor = $this->value->getResource();

                return $this->bindTo($descriptor);

            case OCI_B_CLOB:
            case OCI_B_BLOB:
                $lob = new OciLob($this->statement->getConnection());
                $this->statement->afterExecute(array($lob, 'save'), $this->value);
                $resource = $lob->getResource();

                return $this->bindTo($resource);

            default:
                return $this->bindTo($this->value);
        }
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function bindTo(&$value)
    {
        return oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $value,
            $this->size,
            $this->type
        );
    }
}
