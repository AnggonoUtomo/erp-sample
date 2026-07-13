<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\Contracts;

use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestionResultV1;

interface DocumentIngestionGateway
{
    public function ingest(IngestDocumentV1 $request): IngestionResultV1;
}
