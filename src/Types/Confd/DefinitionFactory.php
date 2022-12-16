<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Definition\Factory as WorkspaceDefinitionFactory;

class DefinitionFactory implements WorkspaceDefinitionFactory
{
    public const TYPES = ['confd'];

    /*
     * example
     * -------
     * confd('harness:/confd'):
     *   - { src: 'docker-compose/.env.twig', dst: 'harness:/.env' }
     *   - { src: 'nginx/app.key.twig',       dst: 'harness:/docker/web/root/etc/ssl/private/app.key' }
     *   - { src: 'nginx/app.crt.twig',       dst: 'harness:/docker/web/root/etc/ssl/certs/app.crt' }
     *   - { src: 'app/auth.json.twig',       dst: 'workspace:/auth.json' }
     *   - { src: 'app/env.php.twig',         dst: 'workspace:/app/etc/env.php' }
     *
     * internal representation
     * -----------------------
     * type: conf
     * metadata:
     *   path: directory where this definition was loaded from
     * declaration: confd($path)
     * body:
     *   - { src: 'location of template in $path', dst: 'target:/path/for/file' }
     */

    /** @var Definition */
    private $prototype;

    /** @var \ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Definition();

        foreach (['path', 'directory', 'templates', 'scope'] as $name) {
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
        $values['directory'] = substr($declaration, 7, -2);
    }

    private function parseBody(array &$values, $body)
    {
        $values['templates'] = $body;
    }

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
