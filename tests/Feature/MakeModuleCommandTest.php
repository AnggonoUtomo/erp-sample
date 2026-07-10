<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeModuleCommandTest extends TestCase
{
    public function test_it_generates_a_module_inside_an_explicit_project(): void
    {
        $backendPath = app_path('Modules/TmpProject/SandboxModule');
        $frontendPath = resource_path('js/pages/tmp-project/sandbox-module');

        File::deleteDirectory($backendPath);
        File::deleteDirectory($frontendPath);

        try {
            $this->artisan('make:module', [
                'name' => 'SandboxModule',
                '--project' => 'TmpProject',
            ])->assertSuccessful();

            $this->assertFileExists($backendPath.'/module.php');
            $this->assertDirectoryExists($backendPath.'/Events');
            $this->assertDirectoryExists($backendPath.'/Integrations');
            $this->assertDirectoryExists($backendPath.'/Listeners');
            $this->assertFileExists($backendPath.'/routes.php');
            $this->assertFileExists($backendPath.'/permissions.php');
            $this->assertFileExists($backendPath.'/navigation.php');
            $this->assertFileExists($backendPath.'/Providers/SandboxModuleServiceProvider.php');
            $this->assertFileExists($backendPath.'/Http/Controllers/SandboxModuleController.php');
            $this->assertFileExists($backendPath.'/Services/SandboxModuleService.php');
            $this->assertFileExists($backendPath.'/Transactions/SandboxModuleTransaction.php');
            $this->assertFileExists($backendPath.'/Support/Permissions.php');
            $this->assertFileExists($frontendPath.'/index.tsx');
        } finally {
            File::deleteDirectory($backendPath);
            File::deleteDirectory($frontendPath);
            File::deleteDirectory(app_path('Modules/TmpProject'));
            File::deleteDirectory(resource_path('js/pages/tmp-project'));
        }
    }

    public function test_it_accepts_project_and_module_shorthand(): void
    {
        $backendPath = app_path('Modules/TmpProject/SandboxModule');

        File::deleteDirectory($backendPath);

        try {
            $this->artisan('make:module', [
                'name' => 'TmpProject:SandboxModule',
                '--without-frontend' => true,
            ])->assertSuccessful();

            $this->assertFileExists($backendPath.'/module.php');
            $this->assertFileExists($backendPath.'/routes.php');
            $this->assertFileDoesNotExist(resource_path('js/pages/tmp-project/sandbox-module/index.tsx'));
        } finally {
            File::deleteDirectory($backendPath);
            File::deleteDirectory(app_path('Modules/TmpProject'));
            File::deleteDirectory(resource_path('js/pages/tmp-project'));
        }
    }
}
