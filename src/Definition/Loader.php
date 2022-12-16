<?php

namespace my127\Workspace\Definition;

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

    public const PATTERN_REPLACE_ENV_VARS = "/\=\{env\(['\"]{1}(?P<name>.*)',[\s]*'(?P<default>.*)['\"]{1}\)\}/";

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
        $file = $this->expandEnvVars($file);

        $files = is_file($file) ? [$file] : $this->getFilesFromPattern($file);

        foreach ($files as $file) {
            foreach ($this->getDeclarationsFromFile($file) as $data) {
                if ($data['type'] === 'import') {
                    $this->loadFromImportDeclaration($data);
                    continue;
                }

                $this->definitions->add($this->factories[$data['type']]->create($data));
            }
        }
    }

    private function getDeclarationsFromFile($file): \Generator
    {
        $directory = dirname($file);
        $scope = $this->resolvePathScope($directory);
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
                        'path' => $directory,
                        'scope' => $scope,
                    ],
                    'declaration' => $declaration,
                    'body' => $body,
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
        $cwd = $data['metadata']['path'];
        $files = is_array($data['body']) ? $data['body'] : [$data['body']];

        foreach ($files as $relativeFile) {
            $this->load($cwd . DIRECTORY_SEPARATOR . $relativeFile);
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

    private function getFilesFromPattern(string $pattern)
    {
        if (($files = glob($pattern)) !== false) {
            return $files;
        }

        throw new Exception("Invalid Pattern '{$pattern}'.");
    }

    private function expandEnvVars(string $file)
    {
        return preg_replace_callback(self::PATTERN_REPLACE_ENV_VARS, [$this, 'replaceEnvVar'], $file);
    }

    private function replaceEnvVar(array $match)
    {
        return getenv($match['name']) ?: $match['default'];
    }
}
