<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WilayahController extends Controller
{

    // Get Provinsi
    public function prov()
    {
        $provinsi = Wilayah::select('kode', 'nama')
            ->whereRaw('CHAR_LENGTH(kode) = 2')
            ->orderBy('nama', 'ASC')
            ->get();
        return response()->json($provinsi);
    }


    // Get Kabupaten
    public function kab($id = null, Request $request = null): JsonResponse
    {
        if (empty($id)) {
            return response()->json([]);
        }

        $query = Wilayah::select('kode', 'nama')->whereRaw('CHAR_LENGTH(kode) = 5');

        if (strlen($id) === 2) {
            $query->whereRaw("LEFT(kode, 2) = '$id'");
        } else {
            $query->where('kode', $id);
        }

        $kabupaten = $query->orderBy('nama', 'ASC')->get();
        return response()->json($kabupaten);
    }

    // Get Kecamatan
    public function kec($id = null): JsonResponse
    {
        if (empty($id)) {
            return response()->json([]);
        }

        $query = Wilayah::select('kode', 'nama')->whereRaw('CHAR_LENGTH(kode) = 8');

        if (strlen($id) === 5) {
            $query->whereRaw("LEFT(kode, 5) = '$id'");
        } else {
            $query->where('kode', $id);
        }

        $kecamatan = $query->orderBy('nama', 'ASC')->get();
        return response()->json($kecamatan);
    }

    // Get Desa
    public function desa($id = null): JsonResponse
    {
        if (empty($id)) {
            return response()->json([]);
        }

        $query = Wilayah::select('kode', 'nama')->whereRaw('CHAR_LENGTH(kode) = 13');

        if (strlen($id) === 8) {
            $query->whereRaw("LEFT(kode, 8) = '$id'");
        } else {
            $query->where('kode', $id);
        }

        $desa = $query->orderBy('nama', 'ASC')->get();
        return response()->json($desa);
    }
}
