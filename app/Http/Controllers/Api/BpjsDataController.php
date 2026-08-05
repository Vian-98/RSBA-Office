<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BpjsService;
use Illuminate\Http\JsonResponse;

class BpjsDataController extends Controller
{
    protected BpjsService $bpjsService;

    public function __construct(BpjsService $bpjsService)
    {
        $this->bpjsService = $bpjsService;
    }

    /**
     * Get wards availability data for middleware consumption.
     */
    public function wards(): JsonResponse
    {
        try {
            $data = $this->bpjsService->getWards();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get operating rooms schedule data for middleware consumption.
     */
    public function rooms(): JsonResponse
    {
        try {
            $data = $this->bpjsService->getOperatingRooms();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
