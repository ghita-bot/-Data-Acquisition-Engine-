<?php

namespace App\Http\Controllers;

use App\Services\DomainIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class DomainController extends Controller
{
    public function __construct(private DomainIntelligenceService $service)
    {
    }

    public function extract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string',
        ]);

        try {
            $data = $this->service->lookup($validated['domain']);
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
