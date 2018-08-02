<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;
use my127\Workspace\Types\Harness\Repository\Definition;
use ReflectionProperty;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    const TYPES = ['harness.repository'];

    /*
     * example
     * -------
     * harness.repository('~'):
     *   magento2:
     *     latest:
     *       url: 'https://github.com/my127/workspace-docker-magento2/master.tar.gz'
     *       type: 'tar.gz'
     *
     * internal representation
     * -----------------------
     * type: harness.repository
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: harness.repository($name)
     * body:
     *   magento2: # name of the harness package
     *     latest: # version of the package
     *       url: https://github.com/my127/workspace-docker-magento2/master.tar.gz
     *       type: tar.gz
     */

    /** @var Definition */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['name', 'packages', 'path', 'scope'] as $name) {
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
        $values['path']  = $metadata['path'];
        $values['scope'] = $metadata['scope'];
    }

    private function parseDeclaration(array &$values, $declaration)
    {
        $values['name'] = substr($declaration, 20, -2);
    }

    private function parseBody(array &$values, $body)
    {
        $values['packages'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
