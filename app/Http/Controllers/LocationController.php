<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class LocationController extends Controller
{
    public function __construct(private LocationService $service)
    {
    }

    public function extract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string',
        ]);

        try {
            $data = $this->service->search($validated['query']);
            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
