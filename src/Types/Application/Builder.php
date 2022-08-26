<?php

namespace my127\Workspace\Types\Application;

use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;

class Builder implements EnvironmentBuilder
{
    /**
     * @var Application
     */
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        $this->application->section('version')
            ->usage('version')
            ->action(function () {
                echo Application::getVersion() . "\n";
            });
    }
}
