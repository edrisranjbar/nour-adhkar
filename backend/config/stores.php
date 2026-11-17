<?php

use App\Services\StoreStats\BazaarInstallProvider;
use App\Services\StoreStats\MyketInstallProvider;

return [
    // Android package id shared by every store listing.
    'package' => env('STORE_PACKAGE', 'ir.adhkar.app'),

    // The dashboard asks for fresh numbers every ten minutes. A store is never contacted more
    // often than this, however many admins have the dashboard open.
    'cache_seconds' => 600,

    // A snapshot is stored whenever the number changes, and at least this often otherwise so the
    // chart has no gaps.
    'heartbeat_minutes' => 10,

    // Stores shown on the dashboard, in order: key => provider class.
    'providers' => [
        'bazaar' => BazaarInstallProvider::class,
        'myket' => MyketInstallProvider::class,
    ],
];
