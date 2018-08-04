<?php

namespace my127\Workspace\Environment;

use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Definition\Loader as DefinitionLoader;
use my127\Workspace\Utility\Filesystem;

class Environment
{
    private $definitions;
    private $loader;
    private $builders;

    /** @var string */
    private $workspacePath;

    /** @var string */
    private $harnessPath;

    public function __construct(DefinitionLoader $loader, DefinitionCollection $definitions, BuilderCollection $builders)
    {
        $this->loader      = $loader;
        $this->definitions = $definitions;
        $this->builders    = $builders;
    }

    public function getWorkspacePath(): string
    {
        return $this->workspacePath;
    }

    public function getHarnessPath(): string
    {
        return $this->harnessPath;
    }

    public function build()
    {
        $this->prepareEnvironmentForBuild();

        /** @var Builder $builder */
        foreach ($this->builders as $builder) {
            $builder->build($this, $this->definitions);
        }
    }

    private function prepareEnvironmentForBuild()
    {
        $this->loadWorkspaceDefinitions();
    }

    private function loadWorkspaceDefinitions()
    {
        $this->loader->setWorkspacePath($this->workspacePath = $this->findWorkspaceDirectory());
        $this->loader->setHarnessPath($this->harnessPath = $this->workspacePath.'/.my127ws');

        $this->loader->load(__DIR__.'/../../config/harness/repository.yml');
        $this->loader->load($this->workspacePath.'/workspace.yml');
        $this->loader->load(home().'/.config/my127/workspace/*.yml');

        $extra = [
            $this->workspacePath.'/workspace.override.yml',
            $this->harnessPath.'/harness.yml'
        ];

        foreach ($extra as $file) {
            if (file_exists($file)) {
                $this->loader->load($file);
            }
        }
    }

    private function getUserHomeDirectory(): string
    {
        return home();
    }

    private function getCurrentWorkingDirectory(): string
    {
        return getcwd();
    }

    private function findWorkspaceDirectory(): string
    {
        $candidate = Filesystem::upsearch('workspace.yml', $this->getCurrentWorkingDirectory());

        if (null !== $candidate) {
            return $candidate;
        }

        return $this->getUserHomeDirectory().'/.my127/workspace';
    }
}
