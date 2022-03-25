<?php

namespace my127\Workspace\Updater;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StdOutput implements Output
{
    /** @var OutputInterface */
    private $output;

    public function __construct()
    {
        $this->output = new SymfonyStyle(new StringInput(''), new ConsoleOutput());
    }

    public function infof(string $info, ...$args): void
    {
        $this->output->writeln('<info>' . sprintf($info, ...$args) . '</info>');
    }

    public function info(string $info): void
    {
        $this->output->writeln('<info>' . $info . '</info>');
    }

    public function success(string $success): void
    {
        $this->output->success($success);
    }
}
