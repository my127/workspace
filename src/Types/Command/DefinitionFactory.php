<?php

namespace my127\Workspace\Types\Command;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    const TYPES = ['command'];

    /*
     * example
     * -------
     * command('assets (download|upload|apply)', 'assets'):
     *   env:
     *     ENVIRONMENT: development
     *   exec: |
     *     #!bash
     *     echo "script to {{ get('workspace.name') }} handle assets"
     *
     * internal representation
     * -----------------------
     * type: command
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: command($usage [, $section])
     * body: string exec|object
     *   env:
     *     KEY: VALUE
     *   exec: string
     */

    /** @var Definition */
    private $prototype;

    /** @var ReflectionProperty[]  */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['usage', 'section', 'env', 'exec', 'description', 'path', 'scope'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Definition::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function create(array $data): WorkspaceDefinition
    {
        $values = [];

        $this->parseMetaData($values, $data['metadata']);
        $this->parseDeclaration($values, $data['declaration']);
        $this->parseBody($values, $data['body']);

        $definition = clone $this->prototype;

        foreach ($this->properties as $name => $property) {
            $property->setValue($definition, $values[$name]);
        }

        return $definition;
    }

    private function parseMetaData(array &$values, array $metadata)
    {
        $values['path']  = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, string $declaration)
    {
        $parts = explode(',', substr($declaration, 8, -1));

        $usage   = substr(trim($parts[0]), 1, -1);
        $section = substr(trim($parts[1]??$parts[0]), 1, -1);

        $values['usage']   = $usage;
        $values['section'] = $section;
    }

    private function parseBody(array &$values, $body)
    {
        if (is_string($body)) {
            $body = [
                'exec' => $body
            ];
        }

        $values['env']  = $body['env']??[];
        $values['description']  = $body['description']??'';
        $values['exec'] = $body['exec'];
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
