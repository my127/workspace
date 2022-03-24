<?php

namespace my127\Workspace\Types\Attribute;

use my127\Workspace\Interpreter\Filter as WorkspaceInterpreterFilter;

class InterpreterFilter implements WorkspaceInterpreterFilter
{
    public const NAME = '@';
    public const PATTERN = '/@\([\'"]?(?P<attribute>[a-zA-Z0-9\._-]+)[\'"]?\)/';

    /** @var Collection */
    private $attributes;

    public function __construct(Collection $attributes)
    {
        $this->attributes = $attributes;
    }

    public function apply(string $script): string
    {
        return preg_replace_callback(self::PATTERN, [$this, 'replace'], $script);
    }

    private function replace(array $match)
    {
        return $this->attributes->get($match['attribute']);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
