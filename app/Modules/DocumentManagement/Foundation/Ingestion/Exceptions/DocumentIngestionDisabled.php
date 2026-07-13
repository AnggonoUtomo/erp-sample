<?php

namespace App\Modules\DocumentManagement\Foundation\Ingestion\Exceptions;

use RuntimeException;

class DocumentIngestionDisabled extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Document ingestion is disabled for this environment.');
    }
}
