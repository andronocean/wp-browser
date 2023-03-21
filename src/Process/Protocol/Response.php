<?php

namespace lucatume\WPBrowser\Process\Protocol;

use lucatume\WPBrowser\Process\StderrStream;
use Opis\Closure\SerializableClosure;
use Throwable;

class Response
{
    private mixed $returnValue;
    private int $exitValue;
    private array $telemetry;
    public static string $stderrValueSeparator = "\r\n\r\n#|worker-stderr-output|#\r\n\r\n";
    private int $stderrLength = 0;

    public function __construct(mixed $returnValue, ?int $exitValue = null, array $telemetry = [])
    {
        if ($exitValue === null) {
            $this->exitValue = $returnValue instanceof Throwable ? 1 : 0;
        } else {
            $this->exitValue = $exitValue;
        }
        $this->returnValue = $returnValue;
        $this->telemetry = $telemetry;
    }

    public static function fromStderr(string $stderrBufferString): self
    {
        // Format: $<return value length>CRLF<return value>CRLF<memory peak usage length>CRLF<memory peak usage>CRLF.
        $payloadLength = strlen($stderrBufferString);
        $separatorPos = strpos($stderrBufferString, self::$stderrValueSeparator);

        if ($separatorPos === false) {
            // No separator found: the worker script did not fail gracefully.
            if ($payloadLength === 0) {
                // No information to build from: it's a failure.
                return new self(null, 1, []);
            }

            // Got something on STDERR: try and build a useful Exception from it.
            $exception = (new StderrStream($stderrBufferString))->getThrowable();
            return new self($exception, 1, []);
        }

        $afterSeparatorBuffer = substr($stderrBufferString, $separatorPos + strlen(self::$stderrValueSeparator));

        [$returnValueClosure, $telemetry] = Parser::decode($afterSeparatorBuffer);

        $returnValue = $returnValueClosure();
        $exitValue = $returnValue instanceof Throwable ? 1 : 0;
        $response = new self($returnValue, $exitValue, $telemetry ?? []);
        $response->stderrLength = $separatorPos;

        return $response;
    }

    public function getPayload(): string
    {
        $returnValue = $this->returnValue;
        $serializableClosure = new SerializableClosure(static function () use ($returnValue) {
            return $returnValue;
        });
        $telemetryData = array_merge($this->telemetry, [
            'memoryPeakUsage' => memory_get_peak_usage()
        ]);
        return Parser::encode([$serializableClosure, $telemetryData]);
    }

    public function getExitValue(): int
    {
        return $this->exitValue;
    }

    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function getTelemetry(): array
    {
        return $this->telemetry;
    }

    public function getStderrLength(): int
    {
        return $this->stderrLength;
    }
}
