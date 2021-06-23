<?php

namespace my127\Workspace\Types\Workspace;

use Exception;
use my127\Workspace\Terminal\Terminal;
use my127\Workspace\Types\Attribute\Collection as AttributeCollection;
use my127\Workspace\Types\Confd\Factory as ConfdFactory;
use my127\Workspace\Types\Crypt\Crypt;
use my127\Workspace\Types\Harness\Harness;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;
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
    private $crypt;

    public const STEP_DOWNLOAD            = 1;
    public const STEP_OVERLAY             = 2;
    public const STEP_VALIDATE_ATTRIBUTES = 3;
    public const STEP_PREPARE             = 4;
    public const STEP_ENABLE_DEPENDENCIES = 5;
    public const STEP_TRIGGER_INSTALLED   = 6;

    private $stepMap = [
        'download'     => self::STEP_DOWNLOAD,
        'overlay'      => self::STEP_OVERLAY,
        'validate'     => self::STEP_VALIDATE_ATTRIBUTES,
        'prepare'      => self::STEP_PREPARE,
        'dependencies' => self::STEP_ENABLE_DEPENDENCIES,
        'installed'    => self::STEP_TRIGGER_INSTALLED
    ];

    public function __construct(
        Workspace $workspace,
        Harness $harness,
        Repository $packages,
        Terminal $terminal,
        AttributeCollection $attributes,
        Path $path,
        ConfdFactory $confd,
        Crypt $crypt)
    {
        $this->workspace  = $workspace;
        $this->packages   = $packages;
        $this->harness    = $harness;
        $this->terminal   = $terminal;
        $this->attributes = $attributes;
        $this->path       = $path;
        $this->confd      = $confd;
        $this->crypt      = $crypt;
    }

    public function getStep(?string $step) {

        if (!isset($this->stepMap[$step])) {
            throw new Exception("Step '{$step}' is not recognised.");
        }

        return $this->stepMap[$step];
    }

    public function install($step = null, $cascade = true, $events = true)
    {
        $package = $this->packages->get($this->workspace->getHarnessName());

        switch ($step) {
            case self::STEP_DOWNLOAD:
                if ($events) {
                    $this->workspace->trigger('before.harness.install');
                }
                $this->downloadAndExtractHarnessPackage($package);
                break;
            case self::STEP_OVERLAY:
                if (($overlayPath = $this->workspace->getOverlayPath()) !== null) {
                    if ($events) {
                        $this->workspace->trigger('before.harness.overlay');
                    }
                    $this->applyOverlayDirectory($overlayPath);
                    if ($events) {
                        $this->workspace->trigger('after.harness.overlay');
                    }
                }
                break;
            case self::STEP_VALIDATE_ATTRIBUTES:
                $this->ensureRequiredAttributesArePresent($this->harness->getRequiredAttributes());
                break;
            case self::STEP_PREPARE:
                if ($events) {
                    $this->workspace->trigger('before.harness.prepare');
                }
                $this->applyConfiguration($this->harness->getRequiredConfdPaths());
                if ($events) {
                    $this->workspace->trigger('after.harness.prepare');
                }
                break;
            case self::STEP_ENABLE_DEPENDENCIES:
                $this->startRequiredServices($this->harness->getRequiredServices());
                if ($events) {
                    $this->workspace->trigger('after.harness.install');
                }
                break;
            case self::STEP_TRIGGER_INSTALLED:
                if ($events) {
                    $this->workspace->trigger('harness.installed');
                }
                break;
        }

        if ($cascade && $step < self::STEP_TRIGGER_INSTALLED) {
            $this->workspace->run('install --step='.($step + 1));
        }
    }

    private function downloadAndExtractHarnessPackage(Package $package)
    {
        $harnessInstallPath = $this->workspace->getPath().'/.my127ws';

        if (!is_dir($harnessInstallPath)) {
            mkdir($harnessInstallPath, 0755, true);
            file_put_contents('.my127ws/harness.tar.gz', file_get_contents($package->getDist()['url']));
            passthru('tar -zxf .my127ws/harness.tar.gz --strip=1 -C .my127ws && rm -f .my127ws/harness.tar.gz');
        }
    }

    private function ensureRequiredAttributesArePresent(array $required)
    {
        $attributes = [
            'standard'      => [],
            'standard_file' => [],
            'secret'        => [],
            'secret_file'   => []
        ];

        foreach (['standard', 'secret'] as $type) {
            foreach ($required[$type] ?? [] as $attribute) {
                if (isset($this->attributes[$attribute]) && $this->attributes[$attribute] !== null) {
                    continue;
                }

                $response = $this->terminal->ask($attribute);
                if (empty($response)) {
                    $response = '';
                }
                $attributes[$type][$attribute] = ($type == 'standard') ?
                    $response : '= decrypt("'.$this->crypt->encrypt($response).'")';
            }
        }

        foreach (['standard_file', 'secret_file'] as $type) {
            foreach ($required[$type] ?? [] as $attribute) {
                if (isset($this->attributes[$attribute]) && $this->attributes[$attribute] !== null) {
                    continue;
                }

                $response = $this->terminal->ask('File path to read for ' . $attribute);
                if (empty($response)) {
                    $attributes[$type][$attribute] = '';
                    continue;
                }

                if (file_exists($response) && is_readable($response) && is_file($response)) {
                    $response = file_get_contents($response);
                } else {
                    throw new Exception('Could not read file "' . $response . '"');
                }
                $attributes[$type][$attribute] = ($type == 'standard_file') ?
                    $response : '= decrypt("'.$this->crypt->encrypt($response).'")';
            }
        }

        array_filter($attributes);

        foreach ($attributes as $attributesOfType) {
            $this->writeOutAttributes('workspace.yml', $attributesOfType);
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
