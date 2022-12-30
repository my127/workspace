<?php

namespace my127\Console\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class is here simply to preserve compatiblity with the PHPUnit
 * tests which use output buffering to capture output.
 */
class EchoOutput implements OutputInterface
{
    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
    {
        foreach ((array) $messages as $message) {
            echo $message;
        }
    }

    public function writeln(string|iterable $messages, int $options = 0): void
    {
        foreach ((array) $messages as $message) {
            echo $message . "\n";
        }
    }

    public function setVerbosity(int $level): void
    {
    }

    public function getVerbosity(): int
    {
        return OutputInterface::VERBOSITY_NORMAL;
    }

    public function isQuiet(): bool
    {
        return false;
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function isVeryVerbose(): bool
    {
        return false;
    }

    public function isDebug(): bool
    {
        return false;
    }

    public function setDecorated(bool $decorated): void
    {
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
    }

    public function getFormatter(): OutputFormatterInterface
    {
        throw new \Exception('Not supported');
    }
}
