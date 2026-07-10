<?php

namespace App\Integration\Support;

use App\Shared\ValueObjects\ModuleIdentifier;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Support\Collection;

class IntegrationRegistry
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function modules(): Collection
    {
        return ModuleRegistry::modules();
    }

    /**
     * @return array<int, ModuleIdentifier>
     */
    public function dependenciesFor(ModuleIdentifier|string $module): array
    {
        $identifier = $module instanceof ModuleIdentifier ? $module : ModuleIdentifier::parse($module);
        $manifest = $this->findModule($identifier);

        return collect($manifest['dependencies'] ?? [])
            ->map(fn (string $dependency) => ModuleIdentifier::parse($dependency))
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string>
     */
    public function publishedEventsFor(ModuleIdentifier|string $module): array
    {
        $identifier = $module instanceof ModuleIdentifier ? $module : ModuleIdentifier::parse($module);
        $manifest = $this->findModule($identifier);

        return collect($manifest['events'] ?? [])
            ->filter(fn (mixed $event) => is_string($event) && class_exists($event))
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string>
     */
    public function listenersFor(string $event): array
    {
        return ModuleRegistry::eventListeners()->get($event, []);
    }

    /**
     * @return array<string, mixed>
     */
    public function findModule(ModuleIdentifier $identifier): array
    {
        $module = $this->modules()->first(
            fn (array $module) => $module['project'] === $identifier->project && $module['name'] === $identifier->module
        );

        return is_array($module) ? $module : [];
    }
}
