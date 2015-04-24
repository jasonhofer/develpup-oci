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
class OciParameter implements Contract\OciBindToInterface, Contract\OciBindAsInterface, Contract\OciAllowNullInterface
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
     * @var mixed
     */
    protected $variable = null;

    /**
     * @var bool
     */
    protected $byReference = false;

    /**
     * @var OciLob
     */
    protected $lob;

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
    protected $bound = false;

    /**
     * @var string
     */
    protected $castTo;

    /**
     * @var bool
     */
    protected $allowNull = false;

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
     * @return Contract\OciBindAsInterface
     */
    public function toValue($val)
    {
        $this->value       = $val;
        $this->byReference = false;
        $this->bound       = false;

        return $this;
    }

    /**
     * @param mixed &$var
     *
     * @return Contract\OciBindAsInterface
     */
    public function toVar(&$var)
    {
        $this->variable    = &$var;
        $this->byReference = true;
        $this->value       = null;

        return $this;
    }

    /**
     * @param int $size
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asString($size = -1)
    {
        $this->setType(SQLT_CHR);
        $this->size   = (int) $size;
        $this->castTo = 'string';

        return $this;
    }

    /**
     * @param int $size
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asInt($size = -1)
    {
        $this->setType(OCI_B_INT);
        $this->size   = (int) $size;
        $this->castTo = 'int';

        return $this;
    }

    /**
     * @return Contract\OciAllowNullInterface
     */
    public function asBool()
    {
        $this->setType(OCI_B_BOL);
        $this->castTo = 'bool';

        return $this;
    }

    /**
     * @param int $size
     *
     * @return Contract\OciAllowNullInterface
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
     * @return Contract\OciAllowNullInterface
     */
    public function asBlob($size = -1)
    {
        $this->setType(OCI_B_BLOB);
        $this->size = (int) $size;

        return $this;
    }

    /**
     *
     */
    public function asCursor()
    {
        $this->setType(OCI_B_CURSOR);
    }

    /**
     *
     */
    public function asRowId()
    {
        $this->setType(OCI_B_ROWID);
    }

    /**
     *
     */
    public function allowNull()
    {
        $this->allowNull = true;
    }

    /**
     * @param int $type
     *
     * @throws OciException
     */
    protected function setType($type)
    {
        if (is_int($this->type) && $this->type !== $type) {
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

        if ($this->bound) {
            return true;
        }

        $this->bound = $this->byReference; // No need to re-bind when bound to a reference.

        switch ($this->type) {
            case OCI_B_CURSOR:
                $this->variable = new OciCursor($this->statement->getConnection());
                $this->size     = -1;
                $resource       = $this->variable->getResource();

                return $this->bindTo($resource);

            case OCI_B_ROWID:
                if ($this->lob instanceof OciRowId) {
                    return true;
                }

                if ($this->variable instanceof OciRowId) {
                    $this->lob = $this->variable;
                } elseif ($this->variable instanceof $lobClass) {
                    $this->lob = new OciRowId($this->statement->getConnection(), $this->variable);
                    // $this->variable = $this->lob; // This behavior might be too unexpected.
                } else {
                    $this->lob      = new OciRowId($this->statement->getConnection());
                    $this->variable = $this->lob;
                }

                $this->size = -1;
                $resource   = $this->lob->getResource();

                return $this->bindTo($resource);

            case OCI_B_CLOB:
            case OCI_B_BLOB:
                $bound = ($this->lob instanceof OciLob);

                if (!$bound) {
                    if ($this->variable instanceof OciLob) {
                        $this->lob = $this->variable;
                    } else {
                        $this->lob = new OciLob($this->statement->getConnection());
                    }
                }

                if ($this->byReference) {
                    $this->statement->offPostExecute($this->name);
                    $this->variable = $this->lob;
                } else {
                    $self = $this;
                    $this->statement->onPostExecute($this->name, function () use ($self) {
                        $self->lob->save($self->value);
                    });
                }

                if ($bound) {
                    return $bound;
                }

                $resource = $this->lob->getResource();

                return $this->bindTo($resource);

            default:
                if ($this->byReference) {
                    return $this->bindTo($this->variable);
                }

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
        if ($this->castTo && !($this->allowNull && null === $value)) {
            settype($value, $this->castTo);
        }

        return oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $value,
            $this->size,
            $this->type
        );
    }

    /**
     * @param Contract\OciParameterVisitorInterface $visitor
     */
    public function accept(Contract\OciParameterVisitorInterface $visitor)
    {
        $visitor->visitParameter($this->name, $this->byReference, $this->variable, $this->value, $this->type);
    }
}
