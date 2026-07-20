<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class DomainIntelligenceService
{
    private string $rdapBaseUrl = 'https://rdap.org/domain/';

    /**
     * @param string $domain
     * @return array
     * @throws Exception
     */
    public function lookup(string $domain): array
    {
        $response = Http::timeout(10)->get($this->rdapBaseUrl . $domain);

        if (! $response->successful()) {
            throw new Exception("Gagal mengambil data RDAP untuk domain: {$domain}");
        }

        $data = $response->json();

        return [
            'domain'        => $data['ldhName'] ?? $domain,
            'registrar'     => $this->extractRegistrar($data),
            'registered_at' => $this->extractEvent($data, 'registration'),
            'expired_at'    => $this->extractEvent($data, 'expiration'),
            'last_updated'  => $this->extractEvent($data, 'last changed'),
            'status'        => $data['status'] ?? [],
            'nameservers'   => $this->extractNameservers($data),
        ];
    }

    private function extractRegistrar(array $data): ?string
    {
        foreach ($data['entities'] ?? [] as $entity) {
            if (in_array('registrar', $entity['roles'] ?? [])) {
                $vcard = $entity['vcardArray'][1] ?? [];
                foreach ($vcard as $field) {
                    if (($field[0] ?? null) === 'fn') {
                        return $field[3] ?? null;
                    }
                }
                return $entity['handle'] ?? null;
            }
        }
        return null;
    }

    private function extractEvent(array $data, string $action): ?string
    {
        foreach ($data['events'] ?? [] as $event) {
            if (($event['eventAction'] ?? '') === $action) {
                return $event['eventDate'] ?? null;
            }
        }
        return null;
    }

    private function extractNameservers(array $data): array
    {
        $result = [];
        foreach ($data['nameservers'] ?? [] as $ns) {
            if (isset($ns['ldhName'])) {
                $result[] = $ns['ldhName'];
            }
        }
        return $result;
    }
}
