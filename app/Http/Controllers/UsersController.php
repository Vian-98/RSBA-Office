<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    // Get list karyawan 
    public function list(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $data =   User::select('users.id', 'users.karyawan_id')
            ->join('sdm_karyawan', 'users.karyawan_id', '=', 'sdm_karyawan.id')
            ->when($search, function ($query) use ($search) {
                $query->where('sdm_karyawan.nama', 'like', '%' . $search . '%');
            })
            ->orderBy('sdm_karyawan.nama', 'asc')
            ->limit(10)
            ->with(['karyawan']) // optional if you still want eager loading
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->karyawan->nama,
                    'description' => $item->karyawan->jabatan?->first()->nama ?? '-'
                ];
            });

        return response()->json($data);
    }
}
