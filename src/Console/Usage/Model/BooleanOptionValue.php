<?php

namespace my127\Workspace\Console\Usage\Model;

class BooleanOptionValue implements OptionValue
{
    const TRUE_VALUES = ['1', 'true'];
    const VALUES = ['1', 'true', '0', 'false'];

    /**
     * @var bool
     */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function create(bool $value): self
    {
        return new self($value);
    }

    public function equals(OptionValue $value): bool
    {
        return $value->value() === $this->value;
    }

    public function value(): bool
    {
        return $this->value;
    }
}
