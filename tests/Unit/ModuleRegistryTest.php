<?php

namespace Tests\Unit;

use App\Support\Modules\ModuleRegistry;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = storage_path('framework/testing/module-registry/'.Str::uuid());
        config(['modules.backend_root' => $this->root]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);

        parent::tearDown();
    }

    public function test_route_files_prefer_presentation_route_and_keep_legacy_fallback(): void
    {
        $legacy = $this->writeModule('Console', 'LegacyModule');
        File::put($legacy.'/routes.php', '<?php return [];');

        $target = $this->writeModule('HR', 'TargetModule');
        File::ensureDirectoryExists($target.'/Presentation/Routes');
        File::put($target.'/Presentation/Routes/web.php', '<?php return [];');
        File::put($target.'/routes.php', '<?php throw new RuntimeException("Legacy route must not load.");');

        $this->assertSame([
            $legacy.DIRECTORY_SEPARATOR.'routes.php',
            $target.DIRECTORY_SEPARATOR.'Presentation'.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'web.php',
        ], ModuleRegistry::routeFiles());
    }

    private function writeModule(string $project, string $name): string
    {
        $path = $this->root.DIRECTORY_SEPARATOR.$project.DIRECTORY_SEPARATOR.$name;
        File::ensureDirectoryExists($path);
        File::put($path.'/module.php', '<?php return '.var_export([
            'name' => $name,
            'project' => $project,
            'enabled' => true,
            'providers' => [],
        ], true).';');

        return $path;
    }
}
