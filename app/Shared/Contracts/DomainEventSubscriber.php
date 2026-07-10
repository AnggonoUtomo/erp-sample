<?php

namespace App\Shared\Contracts;

interface DomainEventSubscriber
{
    public function handle(DomainEvent $event): void;
}
