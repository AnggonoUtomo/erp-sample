<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\Fakes;

use App\Modules\HR\EmployeeDocuments\Integration\Contracts\DocumentReferenceReader;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;
use InvalidArgumentException;

final class InMemoryDocumentReferenceReader implements DocumentReferenceReader
{
    private bool $unavailable = false;

    /** @param array<string, string> $statesByReference @param list<string> $deniedActorReferences */
    public function __construct(private array $statesByReference = [], private array $deniedActorReferences = []) {}

    public function setUnavailable(bool $unavailable): void
    {
        $this->unavailable = $unavailable;
    }

    public function describe(DocumentReferenceV1 $reference, string $actorReference): DocumentReferenceDescriptorV1
    {
        if ($actorReference === '') {
            throw new InvalidArgumentException('Actor reference is required.');
        }

        $state = match (true) {
            $this->unavailable => 'UNAVAILABLE',
            in_array($actorReference, $this->deniedActorReferences, true) => 'DENIED',
            default => $this->statesByReference[$reference->value()] ?? 'MISSING',
        };

        return new DocumentReferenceDescriptorV1($reference, $state);
    }
}
