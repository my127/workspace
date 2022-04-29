<?php

namespace my127\Workspace\Plugin\TraefikEndpoints;

use Closure;
use my127\Console\Application\Application;
use my127\Console\Application\Plugin\Plugin;
use my127\Console\Usage\Input;
use Symfony\Component\Console\Output\ConsoleOutput;

class TraefikEndpointsPlugin implements Plugin
{
    /**
     * @var TraefikEndpointProvider
     */
    private $provider;

    /**
     * @var ConsoleOutput
     */
    private $output;

    public function __construct(TraefikEndpointProvider $provider, ConsoleOutput $output)
    {
        $this->provider = $provider;
        $this->output = $output;
    }

    public function setup(Application $application): void
    {
        $application->section('traefik urls')
            ->description('List the Traefik endpoints')
            ->action($this->action());
    }

    /**
     * @return Closure(): <missing>
     */
    private function action(): Closure
    {
        return function (Input $args) {
            foreach ($this->provider->links() as $link) {
                $this->output->writeln($link);
            }
        };
    }
}
