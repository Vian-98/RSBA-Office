<?php

$out = [];
$out[] = "Start: " . date('Y-m-d H:i:s');

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $client = app(App\Services\Docstore\DocstoreClient::class);
    $out[] = "1. Testing M2M Token...";
    $token = $client->getM2mToken();
    $out[] = "M2M Token: " . ($token ? substr($token, 0, 20) . '...' : 'NULL');

    $pkl = App\Models\Surat\SuratBalasanPkl::latest()->first();
    if ($pkl) {
        $out[] = "2. Found PKL ID: {$pkl->id}, No: {$pkl->no}, Current Key: " . ($pkl->docstore_key ?: 'NULL');
        $sync = app(App\Services\DocstoreSyncService::class);
        $res = $sync->syncBalasanPkl($pkl);
        $out[] = "Sync result: " . ($res ? 'SUCCESS' : 'FAILED');
        $pkl->refresh();
        $out[] = "New Docstore Key: " . ($pkl->docstore_key ?: 'NULL');

        if ($pkl->docstore_key) {
            $fetch = $sync->fetchFromDocstore($pkl->docstore_key);
            $out[] = "Fetch success: " . ($fetch['success'] ?? 'false');
            $out[] = "Fetch content type: " . ($fetch['document']['type'] ?? 'none');
        }
    } else {
        $out[] = "No PKL found";
    }
} catch (\Throwable $e) {
    $out[] = "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

file_put_contents(__DIR__ . '/test_out.txt', implode("\n", $out));
