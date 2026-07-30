<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Akreditasi\AkreChapter;
use App\Models\Akreditasi\AkreBabElement;
use App\Models\Akreditasi\AkreElement;

class AkreditasiController extends Controller
{
    public function chapters($kegiatan, Request $request): JsonResponse
    {
        $search = $request->input('search');

        $chapters = AkreChapter::where('kegiatan_id', $kegiatan)
            ->when(
                $search,
                function ($query, $search) {
                    $query->where('singkatan', 'like', "%$search%");
                }
            )
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'singkatan' => $item->singkatan,
                    'description' => $item->nama
                ];
            });

        return response()->json($chapters);
    }

    public function babs($chapter_id, Request $request, $type = null): JsonResponse
    {
        $search = $request->input('search');

        $babs = AkreBabElement::where('chapter_id', $chapter_id)
            ->when(
                $type,
                function ($query, $type) {
                    $query->where('bab', $type);
                }
            )
            ->when(
                $search,
                function ($query, $search) {
                    $query->where('nama', 'like', "%$search%");
                }
            )
            ->orderBy('no')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'description' => Str::limit($item->deskripsi, 50, '...')
                ];
            });

        return response()->json($babs);
    }

    public function elements($sub, Request $request): JsonResponse
    {
        $search = $request->input('search');
        $element = AkreElement::where('akre_bab_id', $sub)
            ->limit(10)
            ->when(
                $search,
                function ($query, $search) {
                    $query->where('element', 'like', "%$search%");
                }
            )
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => "EP " . Str::upper($item->nomor),
                    'description' => Str::limit($item->element, 50, '...')
                ];
            });

        return response()->json($element);
    }

    public function documents($element, Request $request): JsonResponse
    {
        $documents = '';

        return response()->json($documents);
    }
}
