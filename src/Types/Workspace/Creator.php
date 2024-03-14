<?php

namespace my127\Workspace\Types\Workspace;

use CzProject\GitPhp\Git;
use my127\Workspace\Types\Crypt\Key;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;
use my127\Workspace\Utility\Filesystem;
use my127\Workspace\Utility\TmpNamType;
use Symfony\Component\Yaml\Yaml;

class Creator
{
    public function __construct(private Repository $packages)
    {
    }

    public function create(string $name, ?string $harness = null, string $dir = null)
    {
        if ($dir === null) {
            $dir = './' . $name;
        }

        if (is_dir($dir)) {
            throw new \Exception("directory '{$dir}' already exists.");
        }

        mkdir($dir);

        $workspaceData = [
            'description' => "generated local workspace for {$name}.",
        ];

        if ($harness !== null) {
            $workspaceData['harnessLayers'] = $this->findHarnessLayers($harness);
        }

        $workspace = $this->toYamlLooseKey("workspace('{$name}')", $workspaceData);
        $override = $this->toYamlLooseKey("key('default')", (new Key('default'))->getKeyAsHex());

        file_put_contents($dir . '/workspace.yml', $workspace);
        file_put_contents($dir . '/workspace.override.yml', $override);
    }

    private function toYamlLooseKey(string $key, mixed $data): string
    {
        // workspace magic keys aren't to be quoted, so just yaml dump it's value
        return $key . ":\n" . preg_replace('/^/m', '  ', Yaml::dump($data, 10, 2));
    }

    private function findHarnessLayers(string $harness): array
    {
        $package = $this->packages->get($harness);

        $harnessLayers = [$harness];

        $harnessData = $this->parseYamlMergeStreams($this->acquireAndExtractHarnessYml($package));
        if (!is_array($harnessData)) {
            throw new \Exception('Could not parse the harness\'s harness.yml file');
        }

        foreach ($harnessData as $key => $value) {
            if (str_starts_with($key, 'harness(')) {
                if (isset($value['parentLayers'])) {
                    if (!is_array($value['parentLayers'])) {
                        throw new \Exception('Could not parse the harness\'s harness.yml file');
                    }
                    $harnessLayers = array_merge($value['parentLayers'], $harnessLayers);
                }
                break;
            }
        }

        return $harnessLayers;
    }

    private function parseYamlMergeStreams($content): array
    {
        // Workspace doesn't follow Yaml spec and merges Yaml documents together
        // Symfony Yaml doesn't support Yaml document boundaries
        $documents = preg_split('/\R---\R/', $content);
        $mergedDocument = [];
        foreach ($documents as $document) {
            $declarations = Yaml::parse($document);
            if (!is_array($declarations)) {
                continue;
            }
            $mergedDocument = array_merge($mergedDocument, $declarations);
        }

        return $mergedDocument;
    }

    private function acquireAndExtractHarnessYml(Package $package): string
    {
        if ($package->getDist()['localsync'] ?? false) {
            return file_get_contents($package->getDist()['url'] . 'harness.yml');
        }

        if ($package->getDist()['git'] ?? false) {
            $packageDirPath = Filesystem::tempname(TmpNamType::PATH);

            $git = new Git();
            $git->cloneRepository($package->getDist()['url'], $packageDirPath, ['-q', '--depth', '1', '--branch', $package->getDist()['ref']]);

            $yaml = file_get_contents($packageDirPath . '/harness.yml');
            Filesystem::rrmdir($packageDirPath);

            return $yaml;
        }

        $packageTarball = tempnam(sys_get_temp_dir(), 'my127ws');
        file_put_contents($packageTarball, file_get_contents($package->getDist()['url']));

        $packageDir = tempnam(sys_get_temp_dir(), 'my127ws');
        if ($packageDir === false) {
            throw new \Exception('Could not create temporary directory for harness');
        }
        \unlink($packageDir);
        if (!mkdir($packageDir, 0700)) {
            throw new \Exception('Could not create temporary ' . $packageDir . ' directory for harness');
        }

        $command = 'tar -zxf ' . escapeshellarg($packageTarball) . ' --strip=1 -C ' . escapeshellarg($packageDir);
        if (passthru($command) === false) {
            throw new \Exception('Could not parse the harness\'s harness.yml file');
        }

        $harnessYaml = file_get_contents($packageDir . '/harness.yml');
        if (is_dir($packageDir)) {
            exec('rm -rf ' . escapeshellarg($packageDir));
        }
        \unlink($packageTarball);

        if ($harnessYaml === false) {
            throw new \Exception('Could not parse the harness\'s harness.yml file');
        }

        return $harnessYaml;
    }
}
