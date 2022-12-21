<?php

namespace my127\Workspace\Interpreter;

use my127\Workspace\Path\Path;

class Interpreter
{
    public const RUNTIME_DETAILS_PATTERN = '/^#!(?P<interpreter>[a-z]+)(\((?P<path>.*)\))?(\|(?P<filters>.*))?$/';

    /** @var Executor[] */
    private $executors = [];

    /** @var Filter[] */
    private $filters = [];

    /** @var Path */
    private $path;

    public function __construct(Path $path)
    {
        $this->path = $path;
    }

    public function addExecutor(Executor $executor): void
    {
        $this->executors[$executor->getName()] = $executor;
    }

    public function addFilter(Filter $filter): void
    {
        $this->filters[$filter->getName()] = $filter;
    }

    public function script(string $script, array $arguments = []): Script
    {
        $runtime = $this->getRuntimeDetails($script);

        $executor = $this->executors[$runtime['interpreter']];
        $path = $this->path->getRealPath($runtime['path']);

        foreach ($runtime['filters'] as $filter) {
            $script = $this->filters[$filter]->apply($script);
        }

        return new Script($executor, $path, $script, $arguments);
    }

    private function getRuntimeDetails(string $script): array
    {
        preg_match(self::RUNTIME_DETAILS_PATTERN, trim(strtok($script, "\n")), $match);

        if (!isset($match['interpreter'])) {
            throw new \Exception('Script does not specify an interpreter e.g. `#!php` or `#!bash`.');
        }

        $runtime['interpreter'] = $match['interpreter'];
        $runtime['path'] = !empty($match['path']) ? $match['path'] : 'cwd:/';
        $runtime['filters'] = !empty($match['filters']) ? explode('|', $match['filters']) : [];

        return $runtime;
    }
}
