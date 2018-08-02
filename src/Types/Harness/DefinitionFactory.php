<?php

namespace my127\Workspace\Types\Harness;

use Exception;
use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    const TYPES = ['harness'];

    /** @var bool */
    private $isDefined = false;

    /** @var Definition */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties;

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'description', 'require', 'path', 'scope'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Definition::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function create(array $data): WorkspaceDefinition
    {
        if ($this->isDefined) {
            throw new Exception("A harness has already been declared.");
        }

        $values = [];

        $this->parseMetaData($values, $data['metadata']);
        $this->parseDeclaration($values, $data['declaration']);
        $this->parseBody($values, $data['body']);

        $definition = clone $this->prototype;

        foreach ($this->properties as $name => $property) {
            $property->setValue($definition, $values[$name]);
        }

        $this->isDefined = true;

        return $definition;
    }

    private function parseMetaData(array &$values, $metadata)
    {
        $values['path']  = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration)
    {
        $values['name'] = substr($declaration, 9, -2);
    }

    private function parseBody(array &$values, $body)
    {
        $values['description'] = $body['description']??null;
        $values['require']     = $body['require']??null;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
