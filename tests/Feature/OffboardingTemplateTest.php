<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['hr.view', 'offboardings.view', 'offboardings.template-manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_creates_template_with_signed_due_offsets_and_ordered_items_atomically(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('offboardings.template-manage');

        $this->actingAs($user)->post(route('hr.offboardings.templates.store'), [
            'code' => 'standard-exit',
            'name' => 'Standard Exit',
            'description' => 'Checklist separation standar.',
            'active' => true,
            'items' => [
                [
                    'title' => 'Mulai handover',
                    'category' => 'OPERATIONS',
                    'required' => true,
                    'due_offset_days' => -14,
                    'default_assignee_role' => 'supervisor',
                ],
                [
                    'title' => 'Exit interview',
                    'category' => 'HR',
                    'required' => false,
                    'due_offset_days' => 0,
                ],
                [
                    'title' => 'Konfirmasi akses ditutup',
                    'category' => 'IT',
                    'required' => true,
                    'due_offset_days' => 1,
                ],
            ],
        ])->assertRedirect(route('hr.offboardings.templates.index'));

        $template = OffboardingTemplate::query()->where('code', 'STANDARD-EXIT')->firstOrFail();

        $this->assertSame(
            ['Mulai handover', 'Exit interview', 'Konfirmasi akses ditutup'],
            $template->items()->pluck('title')->all(),
        );
        $this->assertSame([-14, 0, 1], $template->items()->pluck('due_offset_days')->all());
        $this->assertSame([0, 1, 2], $template->items()->pluck('sort_order')->all());
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'hr.offboardings',
            'event' => 'OffboardingTemplate.created',
            'auditable_id' => $template->id,
        ]);
    }

    public function test_invalid_duplicate_or_unbounded_input_creates_no_partial_items(): void
    {
        OffboardingTemplate::query()->create([
            'code' => 'EXISTING',
            'name' => 'Existing',
            'active' => true,
        ]);
        $user = User::factory()->create();
        $user->givePermissionTo('offboardings.template-manage');

        $this->actingAs($user)->post(route('hr.offboardings.templates.store'), [
            'code' => 'existing',
            'name' => 'Duplicate',
            'items' => [
                [
                    'title' => '',
                    'category' => 'HR',
                    'required' => true,
                    'due_offset_days' => 366,
                ],
            ],
        ])->assertSessionHasErrors(['code', 'items.0.title', 'items.0.due_offset_days']);

        $this->assertDatabaseCount('hr_offboarding_templates', 1);
        $this->assertDatabaseCount('hr_offboarding_template_items', 0);
    }

    public function test_transaction_rolls_back_template_and_items_when_audit_fails(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('offboardings.template-manage');
        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')
                ->once()
                ->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($user)->post(route('hr.offboardings.templates.store'), [
            'code' => 'ROLLBACK',
            'name' => 'Rollback',
            'items' => [
                [
                    'title' => 'Handover',
                    'category' => 'HR',
                    'required' => true,
                    'due_offset_days' => -7,
                ],
            ],
        ])->assertServerError();

        $this->assertDatabaseCount('hr_offboarding_templates', 0);
        $this->assertDatabaseCount('hr_offboarding_template_items', 0);
    }

    public function test_list_is_paginated_ordered_and_permission_protected(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $unauthorized = User::factory()->create();

        foreach (range(1, 21) as $number) {
            OffboardingTemplate::query()->create([
                'code' => "EXIT-{$number}",
                'name' => sprintf('Template %02d', $number),
                'active' => true,
            ]);
        }

        $this->actingAs($viewer)
            ->get(route('hr.offboardings.templates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/offboardings/templates/index')
                ->has('templates.data', 20)
                ->where('templates.total', 21)
                ->where('templates.last_page', 2));

        $this->actingAs($unauthorized)
            ->get(route('hr.offboardings.templates.index'))
            ->assertForbidden();
        $this->actingAs($unauthorized)
            ->post(route('hr.offboardings.templates.store'))
            ->assertForbidden();
        auth()->logout();
        $this->post(route('hr.offboardings.templates.store'))
            ->assertRedirect(route('hr.login'));
    }
}
