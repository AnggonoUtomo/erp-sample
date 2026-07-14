<?php

namespace Tests\Feature;

use Tests\TestCase;

class OnboardingIntegrationContractTest extends TestCase
{
    public function test_integration_contract_remains_deferred_without_an_approved_consumer(): void
    {
        $module = require base_path('app/Modules/HR/Onboardings/module.php');

        $this->assertSame([], $module['events']);
        $this->assertSame([], $module['listeners']);
        $this->assertNotContains('Attendance', $module['dependencies']);
        $this->assertNotContains('Attendance', $module['integrations']['optional_dependencies']);
        $this->assertDirectoryDoesNotExist(base_path('app/Modules/HR/Onboardings/Contracts/Integration'));
    }
}
