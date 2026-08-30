<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Ruangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicTicketController extends Controller
{
    /**
     * Store a new public complaint / maintenance ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ruangan_id'     => 'required|integer|exists:ruangan,id',
            'jenis'          => 'required|in:umum,it',
            'deskripsi'      => 'required|string|min:10|max:1000',
            'pelapor_nama'   => 'nullable|string|max:100',
            'pelapor_kontak' => 'nullable|string|max:100',
        ], [
            'ruangan_id.required' => 'Silakan pilih lokasi / ruangan.',
            'ruangan_id.exists'   => 'Ruangan yang dipilih tidak valid.',
            'jenis.required'      => 'Pilih kategori kerusakan (Umum / IT).',
            'jenis.in'            => 'Kategori kerusakan harus berupa umum atau it.',
            'deskripsi.required'  => 'Deskripsi kerusakan wajib diisi.',
            'deskripsi.min'       => 'Deskripsi kerusakan minimal 10 karakter.',
            'deskripsi.max'       => 'Deskripsi kerusakan maksimal 1000 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $ticket = MaintenanceRequest::create([
            'ruangan_id'     => $request->input('ruangan_id'),
            'jenis'          => $request->input('jenis'),
            'note'           => $request->input('deskripsi'),
            'pelapor_nama'   => $request->input('pelapor_nama'),
            'pelapor_kontak' => $request->input('pelapor_kontak'),
            'status'         => 'pending',
            'priority'       => 'normal',
        ]);

        $ticket->load('ruangan');

        $formattedData = $this->formatTicketData($ticket);

        return response()->json([
            'message'       => 'Pengaduan berhasil dibuat.',
            'tracking_code' => $ticket->nomor_tiket,
            'data'          => $formattedData,
        ], 201);
    }

    /**
     * Show tracking details for a specific ticket.
     */
    public function show(string $trackingCode): JsonResponse
    {
        $ticket = MaintenanceRequest::with('ruangan')
            ->where('nomor_tiket', trim($trackingCode))
            ->first();

        if (!$ticket) {
            return response()->json([
                'message' => 'Tiket dengan kode pelacakan tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Tiket ditemukan.',
            'data'    => $this->formatTicketData($ticket),
        ], 200);
    }

    /**
     * Format MaintenanceRequest model to match Frontend TicketData schema.
     */
    private function formatTicketData(MaintenanceRequest $ticket): array
    {
        $rawStatus = strtolower($ticket->status ?? 'pending');
        
        $status = match ($rawStatus) {
            'approved', 'proses' => 'proses',
            'completed', 'selesai', 'rejected' => 'selesai',
            default => 'pending',
        };

        $statusLabel = match ($status) {
            'proses'  => 'Diproses',
            'selesai' => 'Selesai',
            default   => 'Menunggu',
        };

        $jenis = $ticket->jenis ?? 'umum';
        $jenisLabel = strtolower($jenis) === 'it' ? 'IT' : 'Umum';

        return [
            'tracking_code' => $ticket->nomor_tiket,
            'jenis'         => $jenis,
            'jenis_label'   => $jenisLabel,
            'deskripsi'     => $ticket->note ?? '',
            'status'        => $status,
            'status_label'  => $statusLabel,
            'pelapor_nama'  => $ticket->pelapor_nama,
            'ruangan'       => $ticket->ruangan?->nama ?? 'Semua Ruangan / Umum',
            'created_at'    => $ticket->created_at ? $ticket->created_at->toDateTimeString() : now()->toDateTimeString(),
            'updated_at'    => $ticket->updated_at ? $ticket->updated_at->toDateTimeString() : now()->toDateTimeString(),
        ];
    }
}
