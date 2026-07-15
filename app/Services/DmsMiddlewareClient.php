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

    public function getInpatientRooms(): array
    {
        try {
            $response = $this->request()->get('/inpatient-rooms');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createInpatientRoom(array $data): array
    {
        $response = $this->request()->post('/inpatient-rooms', $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal membuat ruangan baru.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function updateInpatientRoom(string $id, array $data): array
    {
        $response = $this->request()->put("/inpatient-rooms/{$id}", $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal memperbarui ruangan.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function deleteInpatientRoom(string $id): bool
    {
        $response = $this->request()->delete("/inpatient-rooms/{$id}");
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal menghapus ruangan.';
            throw new \Exception($err);
        }
        return true;
    }

    // ──── Polyclinic CRUD ────────────────────────────────────────────────────────

    public function getPolyclinics(): array
    {
        try {
            $response = $this->request()->get('/polyclinics');
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createPolyclinic(array $data): array
    {
        $response = $this->request()->post('/polyclinics', $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? $response->json('message') ?? 'Gagal membuat poliklinik.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function updatePolyclinic(string $id, array $data): array
    {
        $response = $this->request()->put("/polyclinics/{$id}", $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal memperbarui poliklinik.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function deletePolyclinic(string $id): bool
    {
        $response = $this->request()->delete("/polyclinics/{$id}");
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal menghapus poliklinik.';
            throw new \Exception($err);
        }
        return true;
    }

    // ──── Polyclinic Doctors CRUD ────────────────────────────────────────────────

    public function getPolyclinicDoctors(string $polyId): array
    {
        try {
            $response = $this->request()->get("/polyclinics/{$polyId}/doctors");
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createPolyclinicDoctor(string $polyId, array $data): array
    {
        $request = $this->request();

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $request = $request->attach(
                'photo',
                fopen($data['photo']->getPathname(), 'r'),
                $data['photo']->getClientOriginalName()
            );
            unset($data['photo']);
        }

        $response = $request->post("/polyclinics/{$polyId}/doctors", $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? $response->json('message') ?? 'Gagal menambahkan dokter.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function updatePolyclinicDoctor(string $polyId, string $id, array $data): array
    {
        $request = $this->request();

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $request = $request->attach(
                'photo',
                fopen($data['photo']->getPathname(), 'r'),
                $data['photo']->getClientOriginalName()
            );
            unset($data['photo']);
        }

        // Use POST with _method=PUT to support multipart form data in PHP for updates
        $data['_method'] = 'PUT';

        $response = $request->post("/polyclinics/{$polyId}/doctors/{$id}", $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? $response->json('message') ?? 'Gagal memperbarui dokter.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function deletePolyclinicDoctor(string $polyId, string $id): bool
    {
        $response = $this->request()->delete("/polyclinics/{$polyId}/doctors/{$id}");
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal menghapus dokter.';
            throw new \Exception($err);
        }
        return true;
    }

    // ──── Polyclinic Queue Management ────────────────────────────────────────────

    public function getPolyclinicQueue(string $polyId, ?string $doctorId = null): array
    {
        try {
            $params = [];
            if ($doctorId) {
                $params['doctor_id'] = $doctorId;
            }
            $response = $this->request()->get("/polyclinics/{$polyId}/queue", $params);
            return $response->successful() ? (array) ($response->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addPatientToQueue(string $polyId, array $data): array
    {
        $response = $this->request()->post("/polyclinics/{$polyId}/queue", $data);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal menambahkan pasien ke antrian.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function updateQueueStatus(string $polyId, string $queueId, string $status): array
    {
        $response = $this->request()->put("/polyclinics/{$polyId}/queue/{$queueId}/status", [
            'status' => $status,
        ]);
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal mengubah status antrian.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function requeuePatient(string $polyId, string $queueId): array
    {
        $response = $this->request()->post("/polyclinics/{$polyId}/queue/{$queueId}/requeue");
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal memanggil ulang pasien.';
            throw new \Exception($err);
        }
        return (array) $response->json('data');
    }

    public function deleteQueueEntry(string $polyId, string $queueId): bool
    {
        $response = $this->request()->delete("/polyclinics/{$polyId}/queue/{$queueId}");
        if (!$response->successful()) {
            $err = $response->json('error.message') ?? 'Gagal menghapus antrian.';
            throw new \Exception($err);
        }
        return true;
    }
}
