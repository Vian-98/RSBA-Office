<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DmsMiddlewareClient
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.dms.base_url', 'http://127.0.0.1:8000'), '/');
        $this->email = config('services.dms.email', 'admin@dms.local');
        $this->password = config('services.dms.password', 'password');
    }

    /**
     * Get or fetch token from Cache/DMS auth endpoint.
     */
    protected function getToken(): ?string
    {
        return Cache::remember('dms_api_token', 3600, function () {
            try {
                $response = Http::timeout(5)->post("{$this->baseUrl}/api/v1/auth/login", [
                    'email'    => $this->email,
                    'password' => $this->password,
                ]);

                if ($response->successful()) {
                    return $response->json('data.token');
                }
            } catch (\Exception $e) {
                // Return null if connection fails
            }
            return null;
        });
    }

    /**
     * Build the authorized Http client.
     */
    protected function request()
    {
        $token = $this->getToken();
        if (!$token) {
            // Force refresh cache just in case it was stale/wrong credentials
            Cache::forget('dms_api_token');
            $token = $this->getToken();
            
            if (!$token) {
                throw new \Exception("Gagal terhubung ke DMS Middleware: Authentikasi Gagal atau Server Offline.");
            }
        }

        return Http::timeout(5)->withToken($token)->baseUrl("{$this->baseUrl}/api/v1");
    }

    public function getDisplays(): array
    {
        try {
            $response = $this->request()->get('/displays');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function registerDisplay(string $displayId, string $name): bool
    {
        $response = $this->request()->post('/displays', [
            'display_id' => $displayId,
            'name' => $name,
        ]);
        
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal mendaftarkan monitor.';
            throw new \Exception($err);
        }

        return true;
    }

    public function updateDisplay(string $displayId, string $name): bool
    {
        $response = $this->request()->put("/displays/{$displayId}", [
            'name' => $name,
        ]);
        
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal mengubah nama monitor.';
            throw new \Exception($err);
        }

        return true;
    }

    public function updateMapping(string $deviceId, string $targetType, string $targetId): bool
    {
        $response = $this->request()->put("/displays/{$deviceId}/mapping", [
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);

        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal memperbarui mapping.';
            throw new \Exception($err);
        }

        return true;
    }

    public function getWards(): array
    {
        try {
            $response = $this->request()->get('/wards');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getRooms(): array
    {
        try {
            $response = $this->request()->get('/rooms');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAuditLogs(): array
    {
        try {
            $response = $this->request()->get('/audit-logs');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function syncWards(): bool
    {
        $response = $this->request()->post('/sync/wards');
        return $response->successful();
    }

    public function syncSchedules(): bool
    {
        $response = $this->request()->post('/sync/schedules');
        return $response->successful();
    }
}
