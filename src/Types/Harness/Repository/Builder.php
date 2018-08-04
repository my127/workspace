<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Types\Harness\Repository\Definition;
use my127\Workspace\Types\Harness\Repository\PackageRepository;

class Builder implements EnvironmentBuilder
{
    /** @var PackageRepository */
    private $harnessPackageRepository;

    public function __construct(PackageRepository $harnessPackageRepository)
    {
        $this->harnessPackageRepository = $harnessPackageRepository;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        foreach ($definitions->findByType(Definition::TYPE) as $definition) {
            /** @var Definition $definition */
            $this->harnessPackageRepository->addSource($definition->getName(), $definition->getPackages());
        }
    }
}
