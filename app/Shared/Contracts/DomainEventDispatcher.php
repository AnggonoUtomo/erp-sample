<?php

namespace App\Shared\Contracts;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent $event): void;

    /**
     * @param  iterable<DomainEvent>  $events
     */
    public function dispatchMany(iterable $events): void;
}
