<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Terminal\Terminal;
use my127\Workspace\Types\Attribute\Collection as AttributeCollection;
use my127\Workspace\Types\Confd\Factory as ConfdFactory;
use my127\Workspace\Types\Harness\Harness;
use my127\Workspace\Types\Harness\Repository\Package;
use my127\Workspace\Types\Harness\Repository\PackageRepository;
use Symfony\Component\Yaml\Yaml;
use my127\Workspace\Path\Path;

class Installer
{
    private $workspace;
    private $packages;
    private $harness;
    private $terminal;
    private $attributes;
    private $path;
    private $confd;

    public function __construct(
        Workspace $workspace,
        Harness $harness,
        PackageRepository $packages,
        Terminal $terminal,
        AttributeCollection $attributes,
        Path $path,
        ConfdFactory $confd)
    {
        $this->workspace  = $workspace;
        $this->packages   = $packages;
        $this->harness    = $harness;
        $this->terminal   = $terminal;
        $this->attributes = $attributes;
        $this->path       = $path;
        $this->confd      = $confd;
    }

    public function install($step = null)
    {
        $package = $this->packages->get($this->workspace->getHarnessName());

        switch ($step??1) {
            case 1:
                $this->workspace->trigger('before.harness.install');
                $this->downloadAndExtractHarnessPackage($package);
                $this->workspace->run('install --from-step=2');
                break;
            case 2:
                if (($overlayPath = $this->workspace->getOverlayPath()) !== null) {
                    $this->applyOverlayDirectory($overlayPath);
                }
                $this->workspace->run('install --from-step=3');
                break;
            case 3:
                $this->ensureRequiredAttributesArePresent($this->harness->getRequiredAttributes());
                $this->workspace->run('install --from-step=4');
                break;
            case 4:
                $this->applyConfiguration($this->harness->getRequiredConfdPaths());
                $this->workspace->run('install --from-step=5');
                break;
            case 5:
                $this->startRequiredServices($this->harness->getRequiredServices());
                $this->workspace->trigger('after.harness.install');
                $this->workspace->run('install --from-step=6');
                break;
            case 6:
                $this->workspace->trigger('harness.installed');
                break;
        }
    }

    private function downloadAndExtractHarnessPackage(Package $package)
    {
        $harnessInstallPath = $this->workspace->getPath().'/.my127ws';

        if (!is_dir($harnessInstallPath)) {
            mkdir($harnessInstallPath, 0755, true);
            file_put_contents('.my127ws/harness.tar.gz', file_get_contents($package->getURL()));
            passthru('tar -zxf .my127ws/harness.tar.gz --strip=1 -C .my127ws && rm -f rm -f .my127ws/harness.tar.gz');
        }
    }

    private function ensureRequiredAttributesArePresent(array $required)
    {
        $attributes = [
            'standard' => [],
            'secret'   => []
        ];

        foreach (['standard', 'secret'] as $type) {
            foreach ($required[$type] ?? [] as $attribute) {
                if (!isset($this->attributes[$attribute])) {
                    $attributes[$type][$attribute] = $this->terminal->ask($attribute);
                }
            }
        }

        if (!empty($attributes['standard'])) {
            $this->writeOutAttributes('workspace.yml', $attributes['standard']);
        }

        if (!empty($attributes['secret'])) {
            $this->writeOutAttributes('workspace.override.yml', $attributes['secret']);
        }
    }

    private function writeOutAttributes($file, $attributes)
    {
        $content = "\n";

        foreach ($attributes as $attribute => $value) {
            $content .= "attribute('{$attribute}'): ".Yaml::dump($value)."\n";
        }

        file_put_contents($this->path->getRealPath('workspace:/'.$file), $content, FILE_APPEND);
    }

    private function applyConfiguration(array $paths)
    {
        foreach ($paths as $path) {
            $this->confd->create($path)->apply();
        }
    }

    private function applyOverlayDirectory(string $getOverlayPath)
    {
        $src = $this->path->getRealPath('workspace:/'.$getOverlayPath).'/';
        $dst = $this->path->getRealPath('harness:/');

        if (is_dir($src)) {
            passthru('rsync -a "'.$src.'" "'.$dst.'"');
        }
    }

    private function startRequiredServices(array $requiredServices)
    {
        foreach ($requiredServices as $service) {
            $this->workspace->exec('ws.service '.$service.' enable');
        }
    }
}
