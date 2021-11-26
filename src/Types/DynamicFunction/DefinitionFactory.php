<?php

namespace my127\Workspace\Types\DynamicFunction;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['function'];

    /** @var Definition */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'exec', 'env', 'path', 'arguments', 'scope'] as $name) {
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

    private function parseMetaData(array &$values, $metadata): void
    {
        $values['path'] = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration): void
    {
        // function('add', [v1, v2])

        $parts = explode(',', substr($declaration, 9, -1), 2);

        $values['name'] = substr(trim($parts[0]), 1, -1);
        $values['arguments'] = [];

        if (isset($parts[1])) {
            $values['arguments'] = explode(',', substr(trim($parts[1]), 1, -1));

            foreach ($values['arguments'] as $k => $v) {
                $values['arguments'][$k] = trim($v);
            }
        }
    }

    private function parseBody(array &$values, $body): void
    {
        if (is_string($body)) {
            $body = [
                'exec' => $body,
            ];
        }

        $values['env'] = $body['env'] ?? [];
        $values['exec'] = trim($body['exec']);
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
