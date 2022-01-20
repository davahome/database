<?php
declare(strict_types=1);

namespace DavaHome\Database\Extension;

class DirectValue
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
