<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\SystemSettings\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use ZipArchive;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['backup-restore.view', 'backup-restore.export', 'backup-restore.restore'] as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (['backup-restore.full-export', 'backup-restore.full-restore'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'backup-restore.view',
            'backup-restore.export',
            'backup-restore.restore',
            'backup-restore.full-export',
            'backup-restore.full-restore',
        ]);
    }

    public function test_authorized_users_can_view_backup_restore_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('backup-restore.index'))
            ->assertOk();
    }

    public function test_guests_cannot_access_backup_restore_endpoints(): void
    {
        $this->get(route('backup-restore.index'))->assertRedirect(route('login'));
        $this->get(route('backup-restore.export'))->assertRedirect(route('login'));
        $this->post(route('backup-restore.restore'))->assertRedirect(route('login'));
        $this->get(route('backup-restore.full.export'))->assertRedirect(route('login'));
        $this->post(route('backup-restore.full.restore'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_without_permissions_cannot_access_backup_restore_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('backup-restore.index'))->assertForbidden();
        $this->actingAs($user)->get(route('backup-restore.export'))->assertForbidden();
        $this->actingAs($user)->post(route('backup-restore.restore'))->assertForbidden();
        $this->actingAs($user)->get(route('backup-restore.full.export'))->assertForbidden();
        $this->actingAs($user)->post(route('backup-restore.full.restore'))->assertForbidden();
    }

    public function test_authorized_users_can_export_settings_backup(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        SystemSetting::query()->create([
            'group' => 'pagination',
            'key' => 'default_per_page',
            'value' => '25',
            'encrypted' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('backup-restore.export'))
            ->assertOk();

        $response->assertJsonPath('schema', 'laravel12-starterkit.settings-backup');
        $response->assertJsonPath('sections.system_settings.0.key', 'default_per_page');
    }

    public function test_authorized_users_can_restore_settings_backup(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $payload = [
            'schema' => 'laravel12-starterkit.settings-backup',
            'version' => 1,
            'sections' => [
                'system_settings' => [
                    [
                        'group' => 'pagination',
                        'key' => 'default_per_page',
                        'value' => '50',
                        'encrypted' => false,
                    ],
                ],
                'notification_templates' => [
                    [
                        'key' => 'custom.notice',
                        'name' => 'Custom Notice',
                        'channel' => 'mail',
                        'subject' => 'Subject',
                        'body' => 'Body',
                        'variables' => ['name'],
                        'active' => true,
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('settings-backup.json', json_encode($payload));

        $this->actingAs($user)
            ->post(route('backup-restore.restore'), [
                'backup' => $file,
                'restore_system_settings' => true,
                'restore_notification_templates' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', [
            'group' => 'pagination',
            'key' => 'default_per_page',
            'value' => '50',
        ]);

        $this->assertDatabaseHas('notification_templates', [
            'key' => 'custom.notice',
            'subject' => 'Subject',
        ]);
    }

    public function test_authorized_users_can_export_full_backup_zip(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('backup-restore.full.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/zip');
    }

    public function test_full_restore_requires_confirmation_text(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $file = UploadedFile::fake()->createWithContent('backup.sql', '-- Laravel 12 Starterkit full database backup');

        $this->actingAs($user)
            ->post(route('backup-restore.full.restore'), [
                'backup' => $file,
                'restore_database' => true,
                'confirmation' => 'WRONG',
            ])
            ->assertSessionHasErrors('confirmation');
    }

    public function test_setting_restore_accepts_json_extension_without_strict_mime(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $payload = [
            'schema' => 'laravel12-starterkit.settings-backup',
            'version' => 1,
            'sections' => [
                'system_settings' => [],
                'notification_templates' => [],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('settings-backup.json', json_encode($payload));

        $this->actingAs($user)
            ->post(route('backup-restore.restore'), [
                'backup' => $file,
                'restore_system_settings' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_setting_restore_explains_unknown_json_schema(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $file = UploadedFile::fake()->createWithContent('settings-backup.json', json_encode([
            'app_name' => 'Demo App',
        ]));

        $this->actingAs($user)
            ->post(route('backup-restore.restore'), [
                'backup' => $file,
                'restore_system_settings' => true,
            ])
            ->assertSessionHasErrors([
                'backup' => 'File JSON valid, tapi bukan Settings Backup aplikasi ini. Field schema tidak ditemukan. Gunakan file dari tombol Download Backup JSON.',
            ]);
    }

    public function test_setting_restore_explains_full_backup_manifest(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $file = UploadedFile::fake()->createWithContent('manifest.json', json_encode([
            'schema' => 'laravel12-starterkit.full-backup',
            'version' => 1,
        ]));

        $this->actingAs($user)
            ->post(route('backup-restore.restore'), [
                'backup' => $file,
                'restore_system_settings' => true,
            ])
            ->assertSessionHasErrors([
                'backup' => 'File JSON ini adalah manifest full backup. Untuk full restore, upload file .zip atau .sql pada panel Full Restore.',
            ]);
    }

    public function test_full_restore_accepts_sql_extension_without_strict_mime(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $file = UploadedFile::fake()->createWithContent('backup.sql', '-- Laravel 12 Starterkit full database backup');

        $this->actingAs($user)
            ->post(route('backup-restore.full.restore'), [
                'backup' => $file,
                'restore_database' => true,
                'confirmation' => 'RESTORE FULL BACKUP',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Full database restore selesai. Silakan login ulang karena session database ikut diperbarui.');
    }

    public function test_full_restore_rejects_arbitrary_sql(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('backup-restore.full-restore');
        $file = UploadedFile::fake()->createWithContent('backup.sql', 'DROP TABLE users;');

        $this->actingAs($user)->post(route('backup-restore.full.restore'), [
            'backup' => $file,
            'restore_database' => true,
            'confirmation' => 'RESTORE FULL BACKUP',
        ])->assertSessionHasErrors('backup');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_full_restore_rejects_zip_path_traversal_before_writing_storage(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('backup-restore.full-restore');
        $path = storage_path('framework/testing/malicious-'.uniqid().'.zip');
        File::ensureDirectoryExists(dirname($path));
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('../escape.txt', 'blocked');
        $zip->addFromString('manifest.json', '{}');
        $zip->close();

        try {
            $file = new UploadedFile($path, 'backup.zip', 'application/zip', null, true);
            $this->actingAs($user)->post(route('backup-restore.full.restore'), [
                'backup' => $file,
                'restore_storage_public' => true,
                'confirmation' => 'RESTORE FULL BACKUP',
            ])->assertSessionHasErrors('backup');
            $this->assertFileDoesNotExist(storage_path('framework/testing/escape.txt'));
        } finally {
            File::delete($path);
        }
    }

    public function test_full_restore_rejects_tampered_database_dump(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('backup-restore.full-restore');
        $path = storage_path('framework/testing/tampered-'.uniqid().'.zip');
        $sql = '-- Laravel 12 Starterkit full database backup'.PHP_EOL.'SELECT 1;';
        $manifest = [
            'schema' => 'laravel12-starterkit.full-backup',
            'version' => 2,
            'integrity' => ['database_sql_sha256' => hash('sha256', 'different contents')],
        ];
        File::ensureDirectoryExists(dirname($path));
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('database.sql', $sql);
        $zip->close();

        try {
            $file = new UploadedFile($path, 'backup.zip', 'application/zip', null, true);
            $this->actingAs($user)->post(route('backup-restore.full.restore'), [
                'backup' => $file,
                'restore_database' => true,
                'confirmation' => 'RESTORE FULL BACKUP',
            ])->assertSessionHasErrors('backup');
            $this->assertDatabaseHas('users', ['id' => $user->id]);
        } finally {
            File::delete($path);
        }
    }

    public function test_full_restore_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('backup-restore.full-restore');

        foreach (range(1, 3) as $attempt) {
            $this->actingAs($user)->post(route('backup-restore.full.restore'), [
                'backup' => UploadedFile::fake()->createWithContent("invalid-{$attempt}.sql", 'invalid'),
                'restore_database' => true,
                'confirmation' => 'WRONG',
            ])->assertRedirect();
        }

        $this->actingAs($user)->post(route('backup-restore.full.restore'), [
            'backup' => UploadedFile::fake()->createWithContent('invalid-4.sql', 'invalid'),
            'restore_database' => true,
            'confirmation' => 'WRONG',
        ])->assertTooManyRequests();
    }
}
