<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$syncService = app(App\Services\DocstoreSyncService::class);
$out = [];
$out[] = "=== SYNC ALL DOCUMENTS TO DOCSTORE ===";

// 1. SuratBalasanPkl
$pkls = App\Models\Surat\SuratBalasanPkl::all();
$out[] = "Found " . count($pkls) . " SuratBalasanPkl";
foreach ($pkls as $pkl) {
    $res = $syncService->syncBalasanPkl($pkl);
    $pkl->refresh();
    $out[] = "PKL ID={$pkl->id}, No={$pkl->no} => " . ($res ? "SUCCESS (Key: {$pkl->docstore_key})" : "FAILED");
}

// 2. SuratBalasanPenelitian
$penelitians = App\Models\Surat\SuratBalasanPenelitian::all();
$out[] = "Found " . count($penelitians) . " SuratBalasanPenelitian";
foreach ($penelitians as $p) {
    $res = $syncService->syncBalasanPenelitian($p);
    $p->refresh();
    $out[] = "Penelitian ID={$p->id}, No={$p->no} => " . ($res ? "SUCCESS (Key: {$p->docstore_key})" : "FAILED");
}

// 3. SuratPerintahTugas
$spts = App\Models\Surat\SuratPerintahTugas::all();
$out[] = "Found " . count($spts) . " SuratPerintahTugas";
foreach ($spts as $spt) {
    $res = $syncService->syncPerintahTugas($spt);
    $spt->refresh();
    $out[] = "SPT ID={$spt->id}, No={$spt->no} => " . ($res ? "SUCCESS (Key: {$spt->docstore_key})" : "FAILED");
}

// 4. SuratCuti
$cutis = App\Models\Surat\SuratCuti::all();
$out[] = "Found " . count($cutis) . " SuratCuti";
foreach ($cutis as $c) {
    $res = $syncService->syncCuti($c);
    $c->refresh();
    $out[] = "Cuti ID={$c->id}, No={$c->no_surat} => " . ($res ? "SUCCESS (Key: {$c->docstore_key})" : "FAILED");
}

// 5. SuratSp3
$sp3s = App\Models\Surat\SuratSp3::all();
$out[] = "Found " . count($sp3s) . " SuratSp3";
foreach ($sp3s as $s) {
    $res = $syncService->syncSp3($s);
    $s->refresh();
    $out[] = "SP3 ID={$s->id}, No={$s->no} => " . ($res ? "SUCCESS (Key: {$s->docstore_key})" : "FAILED");
}

file_put_contents(__DIR__ . '/sync_all_out.txt', implode("\n", $out));
echo "DONE SYNC ALL";
