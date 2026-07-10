<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEventDispatcher;

trait PublishesDomainEvents
{
    public function publishRecordedDomainEvents(DomainEventDispatcher $dispatcher): void
    {
        $dispatcher->dispatchMany($this->releaseDomainEvents());
    }
}
