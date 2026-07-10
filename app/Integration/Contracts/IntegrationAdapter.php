<?php

namespace App\Integration\Contracts;

use App\Shared\Contracts\DomainEvent;
use App\Shared\Support\Result;
use App\Shared\ValueObjects\ModuleIdentifier;

interface IntegrationAdapter
{
    public function source(): ModuleIdentifier;

    public function target(): ModuleIdentifier;

    public function canHandle(DomainEvent $event): bool;

    public function handle(DomainEvent $event, IntegrationContext $context): Result;
}
