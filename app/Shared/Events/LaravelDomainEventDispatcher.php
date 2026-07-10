<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEvent;
use App\Shared\Contracts\DomainEventDispatcher;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class LaravelDomainEventDispatcher implements DomainEventDispatcher
{
    public function __construct(
        private Dispatcher $events,
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->events->dispatch($event);
    }

    /**
     * @param  iterable<DomainEvent>  $events
     */
    public function dispatchMany(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
