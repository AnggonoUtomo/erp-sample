<?php

namespace Tests\Feature;

use Tests\TestCase;

class OffboardingIntegrationContractTest extends TestCase
{
    public function test_integration_event_remains_deferred_without_an_approved_consumer(): void
    {
        $module = require base_path('app/Modules/HR/Offboardings/module.php');

        $this->assertSame([], $module['events']);
        $this->assertSame([], $module['listeners']);

        $runtimeDependencies = [
            ...$module['dependencies'],
            ...($module['integrations']['optional_dependencies'] ?? []),
        ];

        $this->assertNotContains('Attendance', $runtimeDependencies);
        $this->assertNotContains('Payroll', $runtimeDependencies);
        $this->assertNotContains('Accounting', $runtimeDependencies);
        $this->assertNotContains('DocumentManagement', $runtimeDependencies);

        $this->assertDirectoryDoesNotExist(base_path('app/Modules/HR/Offboardings/Integration/Events'));
        $this->assertDirectoryDoesNotExist(base_path('app/Modules/HR/Offboardings/Integration/Schemas'));
    }
}
