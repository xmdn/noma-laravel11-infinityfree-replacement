<?php

namespace App\Domain\Shared;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $cents, public string $currency = 'USD')
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function subtract(self $other): self
    {
        return new self(max(0, $this->cents - $other->cents), $this->currency);
    }

    public function multiply(int $quantity): self
    {
        return new self($this->cents * max(0, $quantity), $this->currency);
    }

    public function formatted(): string
    {
        $symbol = match ($this->currency) {
            'EUR' => 'EUR ',
            'GBP' => 'GBP ',
            'UAH' => 'UAH ',
            default => '$',
        };

        return $symbol.number_format($this->cents / 100, 2);
    }
}
