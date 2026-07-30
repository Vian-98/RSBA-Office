<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// jalankan reset cuti setiap hari jam 00.01
Schedule::command('cuti:reset')
    ->dailyAt('00:30')
    ->withoutOverlapping() //cegah overlap jika job sebelumnya masih berjalan
    ->runInBackground() //jalankan di background
    ->appendOutputTo(storage_path('logs/cuti-reset.log'))
    ->description('Reset cuti tahunan berdasarkan tgl masuk karyawan');

// jalankan pengecekan pengiriman otomatis slip gaji setiap menit (diniatkan sesuai jam dinamis di UI)
Schedule::command('payroll:send-scheduled-slips')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/payroll-auto-send.log'))
    ->description('Pengiriman otomatis slip gaji karyawan via email');




