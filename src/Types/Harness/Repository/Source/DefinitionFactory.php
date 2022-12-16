<?php

namespace my127\Workspace\Types\Harness\Repository\Source;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['harness.repository.source'];

    /*
     * example
     * -------
     * harness.repository.source('name'): https://example.com/harnesses.json
     *
     * internal representation
     * -----------------------
     * type: harness.repository.source
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: harness.repository.source('name')
     * body:
     *   https://example.com/harnesses.json
     */

    private $prototype;

    /** @var \ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'url', 'path', 'scope'] as $name) {
            $this->properties[$name] = new \ReflectionProperty(Definition::class, $name);
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

    private function parseMetaData(array &$values, $metadata)
    {
        $values['path'] = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration)
    {
        $values['name'] = substr($declaration, 27, -2);
    }

    private function parseBody(array &$values, $body)
    {
        $values['url'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
