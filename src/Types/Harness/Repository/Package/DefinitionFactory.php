<?php

namespace my127\Workspace\Types\Harness\Repository\Package;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['harness.repository.package'];

    /*
     * example
     * -------
     * harness.repository.package('vendor/name:version'):
     *   type: tar.gz
     *   url: https://github.com/vendor/name/tarball/version
     *
     * internal representation
     * -----------------------
     * type: harness.repository.package
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: harness.repository.package('vendor/name:version')
     * body:
     *   type: tar.gz
     *   url: https://github.com/vendor/name/tarball/version
     */

    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'version', 'dist', 'path', 'scope'] as $name) {
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

    private function parseMetaData(array &$values, $metadata)
    {
        $values['path'] = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration)
    {
        list($name, $version) = explode(':', substr($declaration, 28, -2));

        $values['name'] = $name;
        $values['version'] = $version;
    }

    private function parseBody(array &$values, $body)
    {
        $values['dist'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
