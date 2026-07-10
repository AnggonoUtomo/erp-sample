<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\SharedKernelException;
use NumberFormatter;

final readonly class Money
{
    public function __construct(
        public int $minorAmount,
        public string $currency = 'IDR',
    ) {
        if ($this->currency === '') {
            throw SharedKernelException::invalidValueObject(self::class, 'currency is required.');
        }
    }

    public static function fromMinor(int $minorAmount, string $currency = 'IDR'): self
    {
        return new self($minorAmount, strtoupper($currency));
    }

    public static function fromMajor(int|float|string $amount, string $currency = 'IDR', int $precision = 2): self
    {
        $normalized = (float) str_replace(',', '.', (string) $amount);

        return new self((int) round($normalized * (10 ** $precision)), strtoupper($currency));
    }

    public function add(self $money): self
    {
        $this->assertSameCurrency($money);

        return new self($this->minorAmount + $money->minorAmount, $this->currency);
    }

    public function subtract(self $money): self
    {
        $this->assertSameCurrency($money);

        return new self($this->minorAmount - $money->minorAmount, $this->currency);
    }

    public function majorAmount(int $precision = 2): float
    {
        return $this->minorAmount / (10 ** $precision);
    }

    public function format(string $locale = 'id_ID'): string
    {
        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

            return $formatter->formatCurrency($this->majorAmount(), $this->currency);
        }

        return $this->currency.' '.number_format($this->majorAmount(), 2, ',', '.');
    }

    /**
     * @return array{minor_amount: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'minor_amount' => $this->minorAmount,
            'currency' => $this->currency,
        ];
    }

    private function assertSameCurrency(self $money): void
    {
        if ($this->currency !== $money->currency) {
            throw SharedKernelException::invalidValueObject(self::class, 'currency mismatch.');
        }
    }
}
