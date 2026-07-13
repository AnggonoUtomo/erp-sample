<?php

namespace App\Modules\DocumentManagement\Foundation\Ingestion\Services;

use App\Modules\DocumentManagement\Foundation\Ingestion\Exceptions\DocumentIngestionDisabled;

class DocumentIngestionAvailability
{
    public function assertEnabled(): void
    {
        if (config('document-management.ingestion_enabled') !== true) {
            throw new DocumentIngestionDisabled;
        }
    }
}
