<?php

namespace my127\Workspace\Types\Attribute;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    const TYPES = ['attribute', 'attributes'];

    /*
     * example
     * -------
     * attribute('database.connection'):
     *   host: mysql
     *   user: root
     *   pass: root
     *   name: application
     *
     * internal representation
     * -----------------------
     * type: attribute
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: attribute($key)
     * body: mixed
     */

    /** @var Definition  */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['key', 'value', 'path', 'scope', 'type'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Definition::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function create(array $data): WorkspaceDefinition
    {
        $values = ['type' => $data['type']];

        $this->parseMetaData($values, $data['metadata']);
        $this->parseDeclaration($values, $data['type'], $data['declaration']);
        $this->parseBody($values, $data['body']);

        $definition = clone $this->prototype;

        foreach ($this->properties as $name => $property) {
            $property->setValue($definition, $values[$name]);
        }

        return $definition;
    }

    private function parseMetaData(array &$values, $metadata)
    {
        $values['path']  = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, string $type, $declaration)
    {
        switch ($type) {

            case 'attribute':
                $values['key'] = substr($declaration, 11, -2);
                break;

            case 'attributes':
                $values['key'] = '~';
                break;
        }
    }

    private function parseBody(array &$values, $body)
    {
        $values['value'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
