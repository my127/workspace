<?php

namespace my127\Workspace\Terminal;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\Question;

class Terminal
{
    private $output;
    private $argv;
    private $question;

    public function __construct(ConsoleOutput $output, ArgvInput $argv, QuestionHelper $question)
    {
        $this->output = $output;
        $this->argv = $argv;
        $this->question = $question;
    }

    public function ask(string $message, $default = null)
    {
        $response = $this->question->ask($this->argv, $this->output, new Question($message . ': '));

        return (!empty($response)) ? $response : $default;
    }

    public function write(string $line)
    {
        $this->output->writeln($line);
    }
}
