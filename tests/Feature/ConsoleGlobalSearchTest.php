<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConsoleGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('global-search:127.0.0.1');

        foreach (['global-search.search', 'users.view'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('searcher')->syncPermissions(['global-search.search']);
        Role::findOrCreate('user-searcher')->syncPermissions(['global-search.search', 'users.view']);
        Role::findOrCreate(User::SUPER_SYSTEM_ROLE)->syncPermissions(['global-search.search', 'users.view']);
    }

    public function test_guest_cannot_search_entities(): void
    {
        $this->getJson(route('global-search.index', ['term' => 'admin']))
            ->assertUnauthorized();
    }

    public function test_user_without_global_search_permission_cannot_search_entities(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('global-search.index', ['term' => 'admin']))
            ->assertForbidden();
    }

    public function test_user_without_provider_permission_receives_empty_user_results(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('searcher');
        User::factory()->create(['name' => 'Visible Target', 'email' => 'target@example.test']);

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'target']))
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_authorized_user_can_search_users_with_safe_read_only_result_shape(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');
        $target = User::factory()->create([
            'name' => 'Ayu Searchable',
            'email' => 'ayu.searchable@example.test',
            'password' => 'plain-text-should-never-leak',
            'remember_token' => 'remember-token-should-never-leak',
        ]);

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'ayu']))
            ->assertOk()
            ->assertJsonPath('data.0.provider', 'users')
            ->assertJsonPath('data.0.type', 'user')
            ->assertJsonPath('data.0.title', 'Ayu Searchable')
            ->assertJsonPath('data.0.group', 'Users')
            ->assertJsonPath('data.0.url', route('users.index', ['search' => $target->name], false))
            ->assertJsonMissingPath('data.0.meta.password')
            ->assertJsonMissingPath('data.0.meta.remember_token');

        $this->assertStringNotContainsString('plain-text-should-never-leak', $this->getJson(route('global-search.index', ['term' => 'ayu']))->getContent());
        $this->assertStringNotContainsString('remember-token-should-never-leak', $this->getJson(route('global-search.index', ['term' => 'ayu']))->getContent());
    }

    public function test_forbidden_result_guard_removes_sensitive_user_results(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');
        User::factory()->create(['name' => 'API Token Owner', 'email' => 'api-token-owner@example.test']);
        User::factory()->create(['name' => 'Safe Owner', 'email' => 'safe.owner@example.test']);

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'owner']))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Safe Owner')
            ->assertJsonCount(1, 'data');
    }

    public function test_super_system_users_are_hidden_from_non_super_system_searchers(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');
        $hidden = User::factory()->create(['name' => 'Root System Account', 'email' => 'root.system@example.test']);
        $hidden->assignRole(User::SUPER_SYSTEM_ROLE);

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'root']))
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_search_results_are_limited_and_deterministic(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');

        User::factory()->create(['name' => 'Search Charlie', 'email' => 'charlie@example.test']);
        User::factory()->create(['name' => 'Search Alpha', 'email' => 'alpha@example.test']);
        User::factory()->create(['name' => 'Search Bravo', 'email' => 'bravo@example.test']);

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'search', 'limit' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Search Alpha')
            ->assertJsonPath('data.1.title', 'Search Bravo');
    }

    public function test_query_must_be_valid(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'a']))
            ->assertUnprocessable();
    }

    public function test_search_endpoint_is_rate_limited(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('user-searcher');

        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($actor)
                ->getJson(route('global-search.index', ['term' => 'search']))
                ->assertOk();
        }

        $this->actingAs($actor)
            ->getJson(route('global-search.index', ['term' => 'search']))
            ->assertTooManyRequests();
    }
}
