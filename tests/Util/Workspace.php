<?php

namespace my127\Workspace\Tests\Util;

use my127\Workspace\Utility\Filesystem;

class Workspace
{
    public function __construct(private string $path, private string $homePath)
    {
        putenv(sprintf('MY127WS_HOME=%s', $this->homePath));
        $_SERVER['MY127WS_HOME'] = $this->homePath;
    }

    public static function create(string $path, string $homePath): self
    {
        if (empty($path)) {
            throw new \RuntimeException('Workspace path cannot be empty');
        }
        if (empty($homePath)) {
            throw new \RuntimeException('Workspace home directory path cannot be empty');
        }

        return new self($path, $homePath);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->path($path));
    }

    public function path(?string $path = null): string
    {
        if ($path === null) {
            return $this->path;
        }

        return implode('/', [$this->path, $path]);
    }

    public function loadSample(string $name): void
    {
        $path = sprintf('%s/../samples/%s', __DIR__, $name);

        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Sample folder "%s" not found.', $name));
        }

        Filesystem::rcopy($path, $this->path());
    }

    public function getContents(string $path): string
    {
        if ($this->exists($path) === false) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $path));
        }

        $contents = file_get_contents($this->path($path));

        if ($contents === false) {
            throw new \RuntimeException('file_get_contents returned false');
        }

        return $contents;
    }

    public function reset(): void
    {
        if (file_exists($this->path)) {
            $this->remove($this->path);
        }

        mkdir($this->path);
    }

    public function put(string $path, string $contents): Workspace
    {
        if (!$this->exists(dirname($path))) {
            $this->mkdir(dirname($path));
        }

        file_put_contents($this->path($path), $contents);

        return $this;
    }

    public function mkdir(string $path): Workspace
    {
        $path = $this->path($path);

        if (file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Node "%s" already exists, cannot create directory', $path));
        }

        mkdir($path, 0777, true);

        return $this;
    }

    private function remove(string $path = ''): void
    {
        if ($path) {
            $splFileInfo = new \SplFileInfo($path);

            if (in_array($splFileInfo->getType(), ['socket', 'file', 'link'])) {
                unlink($path);

                return;
            }
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $this->remove($file->getPathName());
        }

        rmdir($path);
    }
}
