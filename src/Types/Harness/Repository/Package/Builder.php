<?php

namespace my127\Workspace\Types\Harness\Repository\Package;

use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Types\Harness\Repository\PackageRepository;

class Builder implements EnvironmentBuilder
{
    private $harnessPackageRepository;

    public function __construct(PackageRepository $harnessPackageRepository)
    {
        $this->harnessPackageRepository = $harnessPackageRepository;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        foreach ($definitions->findByType(Definition::TYPE) as $definition) {
            /* @var Definition $definition */
            $this->harnessPackageRepository->addPackage(
                $definition->getName(),
                $definition->getVersion(),
                $definition->getDist()
            );
        }
    }
}
