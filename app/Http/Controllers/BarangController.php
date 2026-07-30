<?php

namespace App\Http\Controllers;

use App\Models\Master\Barang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BarangController extends Controller
{
    function list(Request $request): JsonResponse
    {
        $search  = $request->get('search');

        $data = Barang::with('kategori')
            ->select('id', 'nama', 'sku', 'kategori_id')
            ->when(
                $search,
                function ($query, $search) {
                    $query->where(
                        function ($q) use ($search) {
                            $searchItem = '%' . $search . '%';

                            $q->where('nama', 'like', $searchItem)
                                ->orWhere('sku', 'like', $searchItem);
                        }
                    );
                }
            )
            ->orderBy('nama', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $kategori = $item->kategori?->nama ?? null;

                $item->description = "$item->sku | {$kategori}";
                return $item;
            });

        return response()->json($data);
    }


    public function listByKategori($kategori, Request $request): JsonResponse
    {
        if ($kategori) {
            // get variable
            $search  = $request->input('search');

            // query
            $query = Barang::with('kategori')
                ->select('id', 'nama', 'sku', 'kategori_id');

            // Where Conditional
            if (isset($kategori)) {
                if ($kategori === 'bhp') {
                    $query->where('bhp', 1);
                } else if ($kategori === 'baru') {
                    $query->where('bhp', 0);
                } else {
                    $query->where('kategori_id', $kategori);
                }
            }

            // Query Concate
            $query->when(
                $search,
                function ($query, $search) {
                    $query->where(
                        function ($q) use ($search) {
                            $searchItem = '%' . $search . '%';

                            $q->where('nama', 'like', $searchItem)
                                ->orWhere('sku', 'like', $searchItem);
                        }
                    );
                }
            )
                ->orderBy('nama', 'asc')
                ->limit(10);


            // get data and mapping
            $data = $query->get()
                ->map(function ($item) {
                    $kategori = $item->kategori?->nama ?? null;

                    $item->description = "$item->sku | {$kategori}";
                    return $item;
                });

            // return
            return response()->json($data);
        } else {
            abort(404);
        }
    }

    public function stok(Request $request): JsonResponse
    {
        $search = $request->input('search');

        $data = Barang::with(
            [
                'kategori',
                'stoks',
                'stoks.penyimpanan',
                'stoks.penerimaanDet.penerimaan'
            ]
        )
            ->withSum('stoks', 'stok')
            ->when(
                $search,
                function ($query, $search) {
                    $query->where('nama', 'like', "%$search%");
                }
            )
            ->orderBy('nama', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'barang' => $item->nama,
                    'description' => $item->kategori->nama . ' | Sisa Stok : ' . $item->stoks_sum_stok,
                    'penyimpanan' => $item->penyimpanan->nama ?? null,
                ];
            });

        return response()->json($data);
    }
}
