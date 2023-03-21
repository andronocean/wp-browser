<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Opis\Closure\SerializableClosure;

class Request
{
    private array $control;
    private SerializableClosure $serializableClosure;

    public function __construct(array $control, SerializableClosure $serializableClosure)
    {
        $this->control = $control;
        $this->serializableClosure = $serializableClosure;
    }

    public function getPayload(): string
    {
        return Parser::encode([$this->control, $this->serializableClosure]);
    }

    public static function fromPayload(string $payload): self
    {
        // Decode only the control now to decode the rest when auto-loading is working.
        [$controlArray] = Parser::decode($payload, 0, 1);

        $control = new Control($controlArray);
        $control->apply();

        [$serializableClosure] = Parser::decode($payload, 1, 1);

        return new self($controlArray, $serializableClosure);
    }

    public function getSerializableClosure(): SerializableClosure
    {
        return $this->serializableClosure;
    }

    public function getControl(): Control
    {
        return new Control($this->control);
    }
}
