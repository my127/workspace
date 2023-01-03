<?php

namespace my127\Console\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class StdErrOutput implements OutputInterface
{
    private OutputInterface $inner;

    public function __construct()
    {
        $this->inner = new StreamOutput(fopen('php://stderr', 'w'));
    }

    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
    {
        $this->inner->write($messages, $newline, $options);
    }

    public function writeln(string|iterable $messages, int $options = 0): void
    {
        $this->inner->writeln($messages, $options);
    }

    public function setVerbosity(int $level): void
    {
        $this->inner->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->inner->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->inner->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->inner->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->inner->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->inner->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->inner->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->inner->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->inner->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->inner->getFormatter();
    }
}
