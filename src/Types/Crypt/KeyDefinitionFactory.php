<?php

namespace my127\Workspace\Types\Crypt;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class KeyDefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['key'];

    /*
     * example
     * -------
     * key('default'): d350b1adbc254bdd0123ec95009f8ae6e1f588dd0a5ea8ec52029e2ee8dd177c
     *
     * internal representation
     * -----------------------
     * type: key
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: key($name)
     * body: key in hex form
     *
     */

    /** @var KeyDefinition */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new KeyDefinition();

        foreach (['name', 'key', 'path', 'scope'] as $name) {
            $this->properties[$name] = new ReflectionProperty(KeyDefinition::class, $name);
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
        $values['name'] = substr($declaration, 5, -2);
    }

    private function parseBody(array &$values, $body)
    {
        $values['key'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
