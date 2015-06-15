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
class OciParameter implements
    Contract\OciBindToInterface,
    Contract\OciBindAsInterface,
    Contract\OciAllowNullInterface,
    Contract\OciBindAsArrayInterface
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
    protected $maxSize = -1;

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
     * @var boolean
     */
    protected $bindAsArray = false;

    /**
     * @var int
     */
    protected $maxArraySize;

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
     * @param int $maxSize
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asString($maxSize = -1)
    {
        $this->setTypeAndMaxSize(SQLT_CHR, $maxSize);
        $this->castTo = 'string';

        return $this;
    }

    /**
     * @param int $maxSize
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asInt($maxSize = -1)
    {
        $this->setTypeAndMaxSize(OCI_B_INT, $maxSize);
        $this->castTo = 'int';

        return $this;
    }

    /**
     * @return Contract\OciAllowNullInterface
     */
    public function asBool()
    {
        $this->setTypeAndMaxSize(OCI_B_BOL, -1);
        $this->castTo = 'bool';

        return $this;
    }

    /**
     * @param int $maxSize
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asClob($maxSize = -1)
    {
        $this->setTypeAndMaxSize(OCI_B_CLOB, $maxSize);

        return $this;
    }

    /**
     * @param int $maxSize
     *
     * @return Contract\OciAllowNullInterface
     */
    public function asBlob($maxSize = -1)
    {
        $this->setTypeAndMaxSize(OCI_B_BLOB, $maxSize);

        return $this;
    }

    /**
     *
     */
    public function asCursor()
    {
        $this->setTypeAndMaxSize(OCI_B_CURSOR, -1);
    }

    /**
     *
     */
    public function asRowId()
    {
        $this->setTypeAndMaxSize(OCI_B_ROWID, -1);
    }

    /**
     * @param int $maxSize
     *
     * @return $this Contract\OciAsArrayInterface
     */
    public function asArray($maxSize = 0)
    {
        $this->bindAsArray  = true;
        $this->maxArraySize = (int) $maxSize;

        return $this;
    }

    /**
     * @param int $maxSize
     */
    public function ofInts($maxSize = -1)
    {
        $this->asInt($maxSize);
    }

    /**
     * @param int $maxSize
     */
    public function ofStrings($maxSize = -1)
    {
        $this->asString($maxSize);
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
     * @param int $maxSize
     *
     * @throws OciException
     */
    protected function setTypeAndMaxSize($type, $maxSize)
    {
        if (is_int($this->type) && $this->type !== $type) {
            throw new OciException('Cannot change parameter type after it has been defined.');
        }

        $this->type    = $type;
        $this->maxSize = (int) $maxSize;
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
                $this->maxSize  = -1;
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

                $this->maxSize = -1;
                $resource      = $this->lob->getResource();

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
                    return true;
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
        if ($this->bindAsArray) {
            settype($value, 'array');

            return @oci_bind_array_by_name(
                $this->statement->getResource(),
                $this->name,
                $value,
                ($this->maxArraySize ?: max(count($value), 1)),
                (empty($value) ? 0 : $this->maxSize),
                $this->type
            );
        }

        if ($this->castTo && !($this->allowNull && null === $value)) {
            settype($value, $this->castTo);
        }

        return @oci_bind_by_name(
            $this->statement->getResource(),
            $this->name,
            $value,
            $this->maxSize,
            $this->type
        );
    }

    /**
     * @param Contract\OciParameterVisitorInterface $visitor
     */
    public function accept(Contract\OciParameterVisitorInterface $visitor)
    {
        $visitor->visitParameter(
            $this->name,
            $this->byReference,
            $this->variable,
            $this->value,
            $this->type,
            $this->bindAsArray
        );
    }
}
