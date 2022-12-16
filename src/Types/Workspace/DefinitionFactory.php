<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['workspace'];

    /*
     * example
     * -------
     * workspace('name'):
     *   description: An example description here
     *   harness: magento2
     *
     * internal representation
     * -----------------------
     * type: workspace
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: workspace($name)
     * body:
     *   description: description of the workspace
     *   harness: optional, harness to use for standardising the workspace
     */

    /** @var bool */
    private $isDefined = false;

    /** @var Definition */
    private $prototype;

    /** @var \ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'description', 'harnessLayers', 'path', 'overlay', 'require', 'scope'] as $name) {
            $this->properties[$name] = new \ReflectionProperty(Definition::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function create(array $data): WorkspaceDefinition
    {
        if ($this->isDefined) {
            throw new \Exception('A workspace has already been declared.');
        }

        $values = [];

        $this->parseMetaData($values, $data['metadata']);
        $this->parseDeclaration($values, $data['declaration']);
        $this->parseBody($values, $data['body']);

        $definition = clone $this->prototype;

        foreach ($this->properties as $name => $property) {
            if (array_key_exists($name, $values)) {
                $property->setValue($definition, $values[$name]);
            }
        }

        $this->isDefined = true;

        return $definition;
    }

    private function parseMetaData(array &$values, $metadata): void
    {
        $values['path'] = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration): void
    {
        $values['name'] = substr($declaration, 11, -2);
    }

    /**
     * @param array<string,mixed> $body
     */
    private function parseBody(array &$values, ?array $body): void
    {
        $values['description'] = null;
        $values['harnessLayers'] = [];
        $values['overlay'] = null;

        if ($body === null) {
            return;
        }

        $values['description'] = $body['description'] ?? null;

        if (array_key_exists('harnessLayers', $body)) {
            $values['harnessLayers'] = $body['harnessLayers'];
        } elseif (array_key_exists('harness', $body)) {
            $values['harnessLayers'] = [$body['harness']];
        }

        $values['overlay'] = $body['overlay'] ?? null;
        $values['require'] = $body['require'] ?? null;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
