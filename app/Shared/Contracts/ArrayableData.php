<?php

namespace App\Shared\Contracts;

interface ArrayableData
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
