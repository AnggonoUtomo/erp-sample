<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEvent;
use App\Shared\Contracts\DomainEventSubscriber;

final class DomainEventMap
{
    /**
     * @var array<class-string<DomainEvent>, array<int, class-string<DomainEventSubscriber>>>
     */
    private array $listeners = [];

    /**
     * @param  class-string<DomainEvent>  $event
     * @param  class-string<DomainEventSubscriber>  $subscriber
     */
    public function listen(string $event, string $subscriber): void
    {
        $this->listeners[$event] ??= [];

        if (! in_array($subscriber, $this->listeners[$event], true)) {
            $this->listeners[$event][] = $subscriber;
        }
    }

    /**
     * @return array<class-string<DomainEvent>, array<int, class-string<DomainEventSubscriber>>>
     */
    public function listeners(): array
    {
        return $this->listeners;
    }

    /**
     * @param  class-string<DomainEvent>  $event
     * @return array<int, class-string<DomainEventSubscriber>>
     */
    public function listenersFor(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }
}
