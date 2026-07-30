<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,

    // Company ID Shared View
    App\Providers\CompanyIdServiceProvider::class,

    // Filament
    App\Providers\FilamentTableServiceProvider::class,

    // Spatie Permission
    Spatie\Permission\PermissionServiceProvider::class,

    // Matwebsite Excel
    Maatwebsite\Excel\ExcelServiceProvider::class,
];
