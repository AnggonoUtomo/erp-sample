<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Project Namespace
    |--------------------------------------------------------------------------
    |
    | Module generator commands use this project when --project is omitted.
    | The value maps to app/Modules/{Project} and resources/js/pages/{project}.
    |
    */

    'default_project' => env('MODULE_DEFAULT_PROJECT', 'Console'),

    'backend_root' => app_path('Modules'),

    'frontend_root' => resource_path('js/pages'),
];
