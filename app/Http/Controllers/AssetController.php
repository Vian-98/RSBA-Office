<?php

namespace App\Http\Controllers;

use App\Models\Assets\AssetBarang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetController extends Controller
{
    function mainItem($in, Request $request): JsonResponse
    {
        $search = $request->input('search');

        $data = AssetBarang::with(['barang'])
            ->select('id', 'kode', 'barang_id')
            ->whereNotNull(['kode'])
            ->where('main_asset_id', null)
            ->where('ruangan_id', $in)
            ->when($search, function ($query) use ($search) {
                $query->where('kode', 'like', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q) use ($search) {
                        $q->where('nama', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->map(
                fn($item) => [
                    'value' => $item->id,
                    'label' => $item?->kode,
                    'description' => "Barang: {$item->barang->nama}",
                ]
            );

        return response()->json($data);
    }

    public function list(Request $request): JsonResponse
    {
        $search = $request->input('search');

        $data = AssetBarang::with(['barang', 'ruangan'])
            ->where('status', '!=', 'diperbaiki')
            ->whereDoesntHave('maintenanceRequests', function ($query) {
                $query->active();
            })
            ->when($search, function ($query) use ($search) {
                $query->where('kode', 'like', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q) use ($search) {
                        $q->where('nama', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('ruangan', function ($q) use ($search) {
                        $q->where('nama', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => ($item->kode ? "{$item->kode} - " : '') . ($item->barang?->nama ?? 'Aset') . ($item->ruangan ? " ({$item->ruangan->nama})" : ''),
                'description' => "Lokasi: " . ($item->ruangan?->nama ?? '-'),
            ]);

        return response()->json($data);
    }
}
