<?php

namespace DavaHome\Database;

class CustomOperator
{
    /** @var string */
    protected $operator = '=';

    /** @var mixed|DirectValue */
    protected $value;

    /**
     * @param string            $operator
     * @param DirectValue|mixed $value
     */
    public function __construct($operator, $value)
    {
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     *
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return DirectValue|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param DirectValue|mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
