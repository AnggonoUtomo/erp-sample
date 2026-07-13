<?php

namespace App\Modules\DocumentManagement\Foundation\Delivery\Exceptions;

use RuntimeException;

class DocumentDeliveryDenied extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Document delivery was denied.');
    }
}
