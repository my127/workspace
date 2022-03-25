<?php

use my127\Console\Console;
use my127\Console\Usage\Model\OptionDefinitionCollection;

/**
 * Usage
 *
 * @param string $definition
 * @param string $cmd
 * @param OptionDefinitionCollection $optionRepository
 *
 * @return false|string[]
 * @throws Exception
 */
function usage($definition, $cmd = null, OptionDefinitionCollection $optionRepository = null)
{
    $result = Console::usage($definition, $cmd, $optionRepository);

    if (is_bool($result))
        return $result;

    if (count($result) == 0)
        return [];

    return explode("\n", (string)$result);
}