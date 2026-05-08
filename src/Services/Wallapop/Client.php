<?php declare(strict_types=1);

namespace App\Services\Wallapop;

use App\Utils\Curl;
use App\Models\Search;

class Client
{
    private const API_BASE = 'https://api.wallapop.com/api/v3/search/section';

    private array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    public function search(Search $search): array
    {
        $response = Curl::new()
            ->setUrl($this->buildUrl($search))
            ->setHeaders($this->headers())
            ->send();

        return json_decode($response, true)['data']['section']['items'] ?? [];
    }

    private function buildUrl(Search $search): string
    {
        $params = [
            'source' => 'side_bar_filters',
            'section_type' => 'organic_search_results',
            'keywords' => $search->keywords,
            'time_filter' => 'lastMonth',
        ];

        if ($search->category_ids) {
            $params['category_id'] = $search->category_ids;
        }

        if ($search->price_min) {
            $params['min_sale_price'] = $search->price_min;
        }

        if ($search->price_max) {
            $params['max_sale_price'] = $search->price_max;
        }

        if ($search->distance) {
            $params['distance'] = (int)$search->distance * 1000;
        }

        if ($search->latitude && $search->longitude) {
            $params['latitude'] = $search->latitude;
            $params['longitude'] = $search->longitude;
        } else {
            // Wallapop heavily restricts or returns 0 results for certain categories (like Cars) if no location is provided.
            // Default to center of Madrid if the user did not specify coordinates.
            $params['latitude'] = 40.4168;
            $params['longitude'] = -3.7038;
        }

        if ($search->is_shippable !== null) {
            $params['is_shippable'] = $search->is_shippable ? 'true' : 'false';
        }

        if (empty($search->extra_filters) === false) {
            $extra = json_decode($search->extra_filters, true);

            if (is_array($extra)) {
                $params = array_merge($params, $extra);
            }
        }

        return self::API_BASE.'?'.http_build_query($params);
    }

    private function headers(): array
    {
        return [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: es,en;q=0.9',
            'Origin: https://es.wallapop.com',
            'Referer: https://es.wallapop.com/',
            'User-Agent: '.$this->userAgents[array_rand($this->userAgents)],
            'X-AppVersion: 75491',
            'X-DeviceOS: 0',
        ];
    }
}
