<?php

namespace my127\Workspace\Definition;

use Generator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Yaml;

class Loader
{
    /** @var Factory[] */
    private $factories = [];

    /** @var Collection */
    private $definitions;

    /** @var string */
    private $workspacePath;

    /** @var string */
    private $harnessPath;

    public function __construct(Collection $definitions)
    {
        $this->definitions = $definitions;
    }

    public function addDefinitionFactory(Factory $factory)
    {
        foreach ($factory::getTypes() as $type) {
            $this->factories[$type] = $factory;
        }
    }

    public function load(string $file)
    {
        foreach ($this->getDeclarationsFromFile($file) as $data) {

            if ($data['type'] === 'import') {
                $this->loadFromImportDeclaration($data);
                continue;
            }

            $this->definitions->add($this->factories[$data['type']]->create($data));
        }
    }

    private function getDeclarationsFromFile($file): Generator
    {
        $directory = dirname($file);
        $scope     = $this->resolvePathScope($directory);
        $documents = preg_split('/\R---\R/', file_get_contents($file));

        foreach ($documents as $document) {

            $declarations = Yaml::parse($document);

            if (!is_array($declarations)) {
                continue;
            }

            foreach ($declarations as $declaration => $body) {

                $data = [
                    'type' => $this->getTypeFromDeclaration($declaration),
                    'metadata' => [
                        'path'  => $directory,
                        'scope' => $scope
                    ],
                    'declaration' => $declaration,
                    'body'        => $body
                ];

                yield $data;
            }
        }
    }

    private function getTypeFromDeclaration(string $declaration): string
    {
        return strtok($declaration, '(');
    }

    private function loadFromImportDeclaration(array $data)
    {
        $cwd   = $data['metadata']['path'];
        $files = is_array($data['body'])?$data['body']:[$data['body']];

        foreach ($files as $relativeFile) {

            if (!file_exists($file = $cwd.DIRECTORY_SEPARATOR.$relativeFile)) {
                throw new Exception("File '{$file}' not found.");
            }

            $this->load($file);
        }
    }

    public function setWorkspacePath(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function setHarnessPath(string $harnessPath)
    {
        $this->harnessPath = $harnessPath;
    }

    private function resolvePathScope(string $directory): int
    {
        if (strpos($directory, $this->harnessPath) === 0) {
            return Definition::SCOPE_HARNESS;
        }

        if (strpos($directory, $this->workspacePath) === 0) {
            return Definition::SCOPE_WORKSPACE;
        }

        return Definition::SCOPE_GLOBAL;
    }
}
