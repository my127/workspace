<?php

namespace my127\Workspace\Utility;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ErrorHandler
{
    public static function registerErrorHandler(): void
    {
        $input = new ArgvInput();
        $output = (new ConsoleOutput())->getErrorOutput();
        $format = new SymfonyStyle($input, $output);

        set_error_handler(function (
            int $code,
            string $message,
            string $file,
            int $line,
            ?array $context = null
        ) use ($format): ?bool {
            $format->error(sprintf(
                '%s in %s:%s',
                $message,
                $file,
                $line
            ));

            exit(255);
        }, E_USER_ERROR);

        set_exception_handler(function (\Throwable $throwable) use ($format, $input): void {
            $format->text(sprintf('%s:%s', $throwable->getFile(), $throwable->getLine()));
            $format->error($throwable->getMessage());

            if ($input->hasParameterOption(['-v'])) {
                $format->block($throwable->getTraceAsString());
            }

            exit(255);
        });
    }
}
