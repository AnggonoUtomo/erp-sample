<?php

namespace App\Modules\HR\EmployeeDocuments\Providers;

use App\Modules\HR\EmployeeDocuments\Console\Commands\DocumentsExpiringCommand;
use App\Modules\HR\EmployeeDocuments\Integration\Adapters\DocumentManagementEmployeeDocumentAttachmentAdapter;
use App\Modules\HR\EmployeeDocuments\Integration\Contracts\EmployeeDocumentAttachmentGateway;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Policies\EmployeeDocumentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmployeeDocumentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeDocumentAttachmentGateway::class, DocumentManagementEmployeeDocumentAttachmentAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(EmployeeDocument::class, EmployeeDocumentPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([DocumentsExpiringCommand::class]);
        }
    }
}
