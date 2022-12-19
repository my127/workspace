<?php

namespace my127\Console\Usage\Parser;

use my127\Console\Usage\Model\Option;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;

class InputSequenceFactory
{
    public function createFrom(
        $symbols,
        OptionDefinitionCollection $definitionRepository,
        ?bool $ignoreMissingOption = false
    ): ?InputSequence {
        $options = [];
        $positional = [];

        for ($i = 0, $max = count($symbols); $i < $max; ++$i) {
            $symbol = $symbols[$i];

            if (empty($symbol)) {
                continue;
            }

            // Command or Argument

            if ($symbol[0] != '-') {
                $positional[] = $symbol;
                continue;
            }

            $size = strlen($symbol);

            // Single Dash

            if ($size == 1) {
                $positional[] = $symbol;
                continue;
            }

            // Double Dash

            if ($size == 2 && $symbol[1] == '-') {
                $positional[] = $symbol;
                continue;
            }

            // Long Option

            if ($symbol[1] == '-') {
                $parts = explode('=', substr($symbol, 2), 2);
                $name = $parts[0];

                $definition = $definitionRepository->find($name);

                if (!$definition) {
                    if ($ignoreMissingOption) {
                        continue;
                    }

                    return null;
                }

                switch ($definition->getType()) {
                    case OptionDefinition::TYPE_BOOL:
                        $options[$definition->getLabel()][] = new Option($name, $definition, true);
                        break;

                    case OptionDefinition::TYPE_VALUE:
                        $value = (isset($parts[1])) ? $parts[1] : $symbols[++$i];
                        $options[$definition->getLabel()][] = new Option($name, $definition, $value);
                        break;
                }

                continue;
            }

            // Short Options

            $shortOptions = substr($symbol, 1);

            while (!empty($shortOptions)) {
                $name = $shortOptions[0];
                $shortOptions = substr($shortOptions, 1);
                $definition = $definitionRepository->find($name);

                if (!$definition) {
                    if ($ignoreMissingOption) {
                        continue;
                    }

                    return null;
                }

                switch ($definition->getType()) {
                    case OptionDefinition::TYPE_BOOL:
                        $options[$definition->getLabel()][] = new Option($name, $definition, true);
                        break;

                    case OptionDefinition::TYPE_VALUE:
                        $value = null;

                        if (!empty($shortOptions)) {
                            $value = ($shortOptions[0] == '=') ? substr($shortOptions, 1) : $shortOptions;
                            $shortOptions = '';
                        }

                        if ($value === null) {
                            $value = $symbols[++$i];
                        }

                        $options[$definition->getLabel()][] = new Option($name, $definition, $value);
                        break;
                }
            }
        }

        $positional = array_reverse($positional);

        foreach ($options as &$optionGroup) {
            $optionGroup = array_reverse($optionGroup);
        }

        return new InputSequence($options, $positional);
    }
}
