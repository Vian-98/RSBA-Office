<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    static function list(Request $request): JsonResponse
    {
        $inputSearch = $request->input('search');
        $limit = $request->input('limit');

        $query = Ruangan::select('id', 'nama')
            ->when($inputSearch, function ($q, $inputSearch) {
                return $q->where('nama', 'like', "%$inputSearch%");
            })
            ->orderBy('nama', 'asc');

        if ($limit) {
            $query->limit((int) $limit);
        }

        $ruangan = $query->get();

        return response()->json($ruangan);
    }
}
