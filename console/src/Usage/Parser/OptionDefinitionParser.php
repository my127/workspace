<?php

namespace my127\Console\Usage\Parser;

use my127\Console\Usage\Model\OptionDefinition;

class OptionDefinitionParser
{
    public function parse($option): OptionDefinition
    {
        $shortName   = null;
        $longName    = null;
        $description = null;
        $type        = OptionDefinition::TYPE_BOOL;
        $default     = null;
        $argument    = null;

        $i      = 0;
        $length = strlen($option);

        modeSelect:
        {
            while ($i < $length) {
                switch ($option[$i]) {
                    case ' ':
                    case ',':
                        ++$i;
                        break;

                    case '-':
                        if ($option[$i + 1] == '-') {
                            $i += 2;
                            goto parseLongName;
                        }

                        ++$i;
                        goto parseShortName;

                        break;

                    default:
                        goto parseDescription;
                }
            }

            goto buildOptionDefinition;
        }

        parseShortName:
        {
            $shortName = $option[$i];
            ++$i;

            if ($i == $length) {
                goto buildOptionDefinition;
            }

            goto hasArgument;
        }

        parseLongName:
        {
            while ($i < $length && (($t = $option[$i]) != ' ' && $t != ',' && $t != '=' )) {
                $longName .= $option[$i++];
            }

            if ($i == $length) {
                goto buildOptionDefinition;
            }

            goto hasArgument;
        }

        hasArgument:
        {
            if ($option[$i] == '=') {
                if ($option[$i + 1] == '<') {
                    ++$i;
                }

                goto parseArgument;
            }

            if ((($k = $i + 1) < $length) && ( (($t = $option[$k]) >= 'A') && ($t <= 'Z') )) {
                goto parseArgument;
            }

            goto modeSelect;
        }

        parseArgument:
        {
            ++$i;

            $argument = '';

            while ($i < $length && ($option[$i] != '>' && $option[$i] != ',' && $option[$i] != ' ')) {
                $argument .= $option[$i++];
            }

            ++$i;

            $type = OptionDefinition::TYPE_VALUE;

            goto modeSelect;
        }

        parseDescription:
        {
            while ($i < $length) {
                $description .= $t = $option[$i++];

                if ($t == '[') {
                    goto hasDefault;
                }
            }

            goto buildOptionDefinition;
        }

        hasDefault:
        {
            $hasDefault = '';

            while ($i < $length) {
                $hasDefault .= $t = $option[$i++];

                if ($t == ':') {
                    $description .= $hasDefault;

                    if ($hasDefault == 'default:') {
                        goto parseDefaultValue;
                    }

                    goto parseDescription;
                }
            }

            goto buildOptionDefinition;
        }

        parseDefaultValue:
        {
            $description .= ' ';
            $i += 1;
            $default = '';

            while ($i < $length) {
                $t = $option[$i++];

                if ($t != ']') {
                    $default .= $t;
                } else {
                    $description .= $default . ']';
                    goto parseDescription;
                }
            }

            goto buildOptionDefinition;
        }

        buildOptionDefinition:
        {
            if ($type == OptionDefinition::TYPE_BOOL && $default === null) {
                $default = false;
            }

            return new OptionDefinition($shortName, $longName, $description, $type, $default, $argument);
        }
    }
}
