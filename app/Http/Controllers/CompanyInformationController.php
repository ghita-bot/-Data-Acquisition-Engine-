<?php

namespace App\Http\Controllers;

use App\Services\WebsiteMetadataService;
use App\Services\DomainIntelligenceService;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CompanyInformationController extends Controller
{
    public function __construct(
        private WebsiteMetadataService $websiteService,
        private DomainIntelligenceService $domainService,
        private LocationService $locationService,
    ) {
    }
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string',
        ]);

        $domain = $validated['domain'];
        $url    = "https://{$domain}";
        $result = Cache::remember("company-info:{$domain}", 3600, function () use ($domain, $url) {
            $website = null;
            $domainInfo = null;
            $location = null;
            $errors = [];

            try {
                $website = $this->websiteService->extract($url);
            } catch (Exception $e) {
                Log::warning("Website extract failed for {$domain}: {$e->getMessage()}");
                $errors['website'] = $e->getMessage();
            }
            try {
                $domainInfo = $this->domainService->lookup($domain);
            } catch (Exception $e) {
                Log::warning("Domain lookup failed for {$domain}: {$e->getMessage()}");
                $errors['domain'] = $e->getMessage();
            }

            try {
                $locationQuery = $website['title'] ?? $domain;
                $location = $this->locationService->search($locationQuery);
            } catch (Exception $e) {
                Log::warning("Location search failed for {$domain}: {$e->getMessage()}");
                $errors['location'] = $e->getMessage();
            }

            return [
                'website'  => $website,
                'domain'   => $domainInfo,
                'location' => $location,
                'errors'   => $errors ?: null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
