<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MetodeBayar;
use Illuminate\Http\Request;

class MetodeBayarController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->input('search');

        $methods = MetodeBayar::when($search, fn($q) => $q->where('nama', 'like', "%{$search}%"))
            ->orderBy('id')
            ->get()
            ->map(fn($m) => [
                'id'    => $m->nama,
                'label' => $m->nama,
                'value' => $m->nama,
            ]);

        return response()->json($methods);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
        ]);

        $nama = trim($request->nama);
        $method = MetodeBayar::firstOrCreate(['nama' => $nama]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'    => $method->nama,
                'label' => $method->nama,
                'value' => $method->nama,
            ]
        ]);
    }
}
