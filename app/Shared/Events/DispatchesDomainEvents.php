<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEvent;

trait DispatchesDomainEvents
{
    use PublishesDomainEvents;

    /**
     * @var array<int, DomainEvent>
     */
    private array $domainEvents = [];

    protected function recordDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return array<int, DomainEvent>
     */
    public function releaseDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
