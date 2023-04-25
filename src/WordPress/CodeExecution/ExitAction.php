<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

class ExitAction implements CodeExecutionActionInterface
{
    private int $exitCode;
    private string $stdout;
    private string $stderr;

    public function __construct(int $exitCode, string $stdout = '', string $stderr = '')
    {
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function getClosure(): \Closure
    {
        $exitCode = $this->exitCode;
        $stdout = $this->stdout;
        $stderr = $this->stderr;

        return static function () use ($exitCode, $stdout, $stderr): mixed {
            fwrite(STDOUT, $stdout, strlen($stdout));
            fwrite(STDERR, $stderr, strlen($stderr));
            exit($exitCode);
        };
    }
}
