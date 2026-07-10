<?php

namespace App\Integration\Contracts;

use App\Integration\DTO\IntegrationMessageData;
use App\Shared\Support\Result;

interface IntegrationProjector
{
    public function project(IntegrationMessageData $message, IntegrationContext $context): Result;
}
