<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class LocationService
{
    private string $nominatimBaseUrl = 'https://nominatim.openstreetmap.org/search';

    /**
     * @param string $query
     * @return array
     * @throws Exception
     */
    public function search(string $query): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'BeraniDigitalTechChallenge/1.0 (github.com/ghitaajmz; brigithapaetricea@gmail.com)',
            ])
            ->get($this->nominatimBaseUrl, [
                'q'      => $query,
                'format' => 'jsonv2',
                'limit'  => 1,
                'addressdetails' => 1,
            ]);

        if (! $response->successful()) {
            throw new Exception("Gagal mengambil data lokasi untuk: {$query}. Status: {$response->status()}. Body: {$response->body()}");
        }

        $results = $response->json();

        if (empty($results)) {
            throw new Exception("Lokasi tidak ditemukan untuk query: {$query}");
        }

        $first = $results[0];

        return [
            'display_name' => $first['display_name'] ?? null,
            'latitude'     => $first['lat'] ?? null,
            'longitude'    => $first['lon'] ?? null,
            'importance'   => $first['importance'] ?? null,
            'osm_type'     => $first['osm_type'] ?? null,
            'address'      => $first['address'] ?? [],
        ];
    }
}
