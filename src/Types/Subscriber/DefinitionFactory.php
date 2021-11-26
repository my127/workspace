<?php

namespace my127\Workspace\Types\Subscriber;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['before', 'on', 'after'];

    /*
     * example
     * -------
     * on('harness.installed'):
     *   env:
     *     ENVIRONMENT: development
     *   exec: |
     *     #!bash
     *     echo "harness is installed"
     *
     * internal representation
     * -----------------------
     * type: before|on|after
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

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['event', 'path', 'env', 'exec', 'type', 'scope'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Definition::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function create(array $data): WorkspaceDefinition
    {
        $values = ['type' => $data['type']];

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
        $values['path'] = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, string $declaration)
    {
        switch ($values['type']) {
            case 'before':
                $event = 'before.' . substr($declaration, 8, -2);
                break;

            case 'after':
                $event = 'after.' . substr($declaration, 7, -2);
                break;

            default:
                $event = substr($declaration, 4, -2);
                break;
        }

        $values['event'] = $event;
    }

    private function parseBody(array &$values, $body)
    {
        if (is_string($body)) {
            $body = [
                'exec' => $body,
            ];
        }

        $values['env'] = $body['env'] ?? [];
        $values['exec'] = $body['exec'];
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
