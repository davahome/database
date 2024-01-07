<?php
declare(strict_types=1);

namespace DavaHome\Database\Extension;

class CustomOperator
{
    protected string $operator = '=';

    /** @var mixed|DirectValue */
    protected mixed $value;

    /**
     * @param string            $operator
     * @param DirectValue|mixed $value
     */
    public function __construct(string $operator, mixed $value)
    {
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return DirectValue|mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param DirectValue|mixed $value
     *
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }
}
