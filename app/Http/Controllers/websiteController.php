<?php

namespace App\Http\Controllers;

use App\Services\WebsiteMetadataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class WebsiteController extends Controller
{
    public function __construct(private WebsiteMetadataService $service)
    {
    }

    public function extract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $data = $this->service->extract($validated['url']);
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