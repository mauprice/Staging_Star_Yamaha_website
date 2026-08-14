<?php

namespace Honda\Catalog\Pricing;

use Honda\Catalog\Http\ThrottledHttpClient;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Calls Honda's same-origin pricing API (the same endpoint their own
 * frontend JS calls client-side to hydrate ride-away pricing) using the
 * `pricing-model-id` GUID that IS present in the static model page HTML.
 * Never throws: a failed or empty response just yields a null price,
 * consistent with every other graceful-degradation path in this package.
 */
class MpePcmPricingClient
{
    public function __construct(
        private readonly ThrottledHttpClient $http,
        private readonly array $config = [],
    ) {}

    /**
     * @return array{cents: ?int, label: ?string}
     */
    public function fetchPrice(string $pricingModelId): array
    {
        if (! ($this->config['pricing']['enabled'] ?? true)) {
            return ['cents' => null, 'label' => null];
        }

        $endpoint = rtrim($this->config['base_url'] ?? '', '/')
            .($this->config['pricing']['endpoint'] ?? '/api/MpePcm/GetMpePcmProduct');

        try {
            $response = $this->http->post($endpoint, [['itemId' => $pricingModelId]]);
            $data = json_decode((string) $response->getBody(), true);
        } catch (Throwable $e) {
            Log::warning('honda-catalog: pricing API request failed', [
                'pricing_model_id' => $pricingModelId,
                'error' => $e->getMessage(),
            ]);

            return ['cents' => null, 'label' => null];
        }

        if (! is_array($data)) {
            return ['cents' => null, 'label' => null];
        }

        $entry = collect($data)->first(fn ($row) => ($row['modelItemId'] ?? null) === $pricingModelId)
            ?? ($data[0] ?? null);

        $price = $entry['productPrice'] ?? null;

        if (! $price || (float) $price <= 0) {
            return ['cents' => null, 'label' => null];
        }

        return [
            'cents' => (int) round((float) $price * 100),
            'label' => $this->config['pricing']['default_label'] ?? 'Ride away from',
        ];
    }
}
