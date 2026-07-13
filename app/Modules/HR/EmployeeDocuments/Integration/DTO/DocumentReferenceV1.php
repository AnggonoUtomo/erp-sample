<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\DTO;

use InvalidArgumentException;

final readonly class DocumentReferenceV1
{
    public function __construct(private string $reference)
    {
        if ($reference === '' || mb_strlen($reference) > 255) {
            throw new InvalidArgumentException('Document reference must contain 1 through 255 characters.');
        }
    }

    public function value(): string
    {
        return $this->reference;
    }
}
