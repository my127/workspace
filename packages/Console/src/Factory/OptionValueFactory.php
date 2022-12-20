<?php

namespace my127\Console\Factory;

use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionValue;
use my127\Console\Usage\Model\StringOptionValue;

class OptionValueFactory
{
    public function createFromType(string $type): OptionValue
    {
        switch ($type) {
            case OptionDefinition::TYPE_BOOL:
                return BooleanOptionValue::create(false);
            case OptionDefinition::TYPE_VALUE:
                return StringOptionValue::create('');
            default:
                throw $this->createInvalidTypeException($type);
        }
    }

    public function createFromTypeAndValue(string $type, string $value): OptionValue
    {
        switch ($type) {
            case OptionDefinition::TYPE_BOOL:
                return BooleanOptionValue::create($this->stringToBoolean($value));
            case OptionDefinition::TYPE_VALUE:
                return StringOptionValue::create($value);
            default:
                throw $this->createInvalidTypeException($type);
        }
    }

    private function createInvalidTypeException(string $type): \InvalidArgumentException
    {
        return new \InvalidArgumentException(sprintf(
            'Option type "%s" is invalid. Valid arguments are "%s"',
            $type,
            implode('","', OptionDefinition::TYPES)
        ));
    }

    private function createInvalidBooleanValueException(string $value): \InvalidArgumentException
    {
        return new \InvalidArgumentException(sprintf(
            'The provided value "%s" not a boolean representation. Valid arguments are "%s"',
            $value,
            implode('","', BooleanOptionValue::VALUES)
        ));
    }

    private function stringToBoolean(string $value): bool
    {
        if (!in_array($value, BooleanOptionValue::VALUES)) {
            throw $this->createInvalidBooleanValueException($value);
        }

        return in_array($value, BooleanOptionValue::TRUE_VALUES, true);
    }
}
