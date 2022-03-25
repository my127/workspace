<?php

namespace my127\Workspace\Console\Usage\Exception;

use Exception;

class NoSuchOptionException extends Exception
{
    public static function createFromOptionName(string $option): self
    {
        return new self(sprintf('The option "%s" does not exist.', $option));
    }
}
