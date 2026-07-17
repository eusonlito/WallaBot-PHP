<?php declare(strict_types=1);

namespace App\Services;

use Throwable;
use App\Models\Search;
use App\Models\Item;
use App\Services\Wallapop\Client as WallapopClient;
use App\Services\Telegram\Client as TelegramClient;
use App\Utils\Logger;
use App\Utils\Error;

class SearchSync
{
    private WallapopClient $wallapop;
    private TelegramClient $telegram;

    public function __construct()
    {
        $this->wallapop = new WallapopClient();
        $this->telegram = new TelegramClient();
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);
        $a = sin($latDiff/2) * sin($latDiff/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff/2) * sin($lonDiff/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function sync(Search $search, bool $notify = true): void
    {
        try {
            $items = $this->wallapop->search($search);

            $searchKeywords = array_filter(explode(' ', helper()->normalize($search->keywords)));
            $maxDistanceKm = isset($search->distance) && is_numeric($search->distance) ? (float)$search->distance : null;
            $hasLocation = !empty($search->latitude) && !empty($search->longitude);

            $excludeKeywords = [];
            if (!empty($search->exclude_keywords)) {
                $excludeKeywords = array_filter(array_map('trim', explode(',', helper()->normalize($search->exclude_keywords))));
            }

            foreach ($items as $data) {
                $title = (string)$data['title'];
                $normalizedTitle = helper()->normalize($title);
                $isValid = true;

                if (!isset($search->title_only) || $search->title_only) {
                    foreach ($searchKeywords as $kw) {
                        if (str_contains($normalizedTitle, $kw) === false) {
                            $isValid = false;
                            break;
                        }
                    }
                }

                if ($isValid && !empty($excludeKeywords)) {
                    $description = (string)($data['description'] ?? '');
                    $normalizedDesc = helper()->normalize($description);
                    $fullText = $normalizedTitle . ' ' . $normalizedDesc;

                    foreach ($excludeKeywords as $exc) {
                        if (str_contains($fullText, $exc)) {
                            $isValid = false;
                            break;
                        }
                    }
                }

                if ($isValid === false) {
                    continue;
                }

                // Strict Distance Filter
                $itemLat = $data['location']['latitude'] ?? null;
                $itemLon = $data['location']['longitude'] ?? null;
                if ($hasLocation && $maxDistanceKm !== null && $itemLat !== null && $itemLon !== null) {
                    $itemDistance = $this->calculateDistance((float)$search->latitude, (float)$search->longitude, (float)$itemLat, (float)$itemLon);
                    if ($itemDistance > $maxDistanceKm) {
                        continue; // Skip items that Wallapop injected from outside the radius
                    }
                }

                $wallapopId = (string)$data['id'];
                $price = (float)$data['price']['amount'];
                $title = (string)$data['title'];
                $urlSlug = (string)$data['web_slug'];

                $existing = Item::findByWallapopId($search->id, $wallapopId);

                if ($existing === null) {
                    Item::create([
                        'search_id' => $search->id,
                        'wallapop_id' => $wallapopId,
                        'title' => $title,
                        'description' => $data['description'] ?? null,
                        'price' => $price,
                        'currency' => $data['price']['currency'] ?? 'EUR',
                        'url' => $urlSlug,
                        'images' => $data['images'] ?? null,
                        'location_city' => $data['location']['city'] ?? null,
                        'location_postal_code' => $data['location']['postal_code'] ?? null,
                        'location_region' => $data['location']['region'] ?? null,
                        'location_country' => $data['location']['country_code'] ?? null,
                        'is_shippable' => $data['shipping']['item_is_shippable'] ?? false,
                        'is_bumped' => isset($data['bump']['type']) && $data['bump']['type'] !== 'none',
                        'is_refurbished' => $data['is_refurbished']['flag'] ?? false,
                        'has_warranty' => $data['has_warranty']['flag'] ?? false,
                        'category_id' => $data['category_id'] ?? null,
                        'wallapop_created_at' => $data['created_at'] ?? null,
                        'wallapop_modified_at' => $data['modified_at'] ?? null,
                        'user_id' => $data['user_id'] ?? null,
                        'type_attributes' => $data['type_attributes'] ?? null,
                    ]);

                    if ($notify) {
                        $msg = "🎯 *Nuevo ítem encontrado para la búsqueda \"{$search->keywords}\"*:\n\n";
                        $msg .= "*{$title}*\n\n";
                        $msg .= "💰 ".number_format($price, 2, ',', '.')." €\n";
                        $msg .= "🔗 https://es.wallapop.com/item/{$urlSlug}";

                        $this->telegram->sendMessage($msg);
                        Logger::info("New item: {$wallapopId} - {$title}");
                    }
                } elseif ($price < $existing->price) {
                    $oldPrice = $existing->price;
                    $existing->updatePrice($price, "Price drop from ".number_format($oldPrice, 2, ',', '.')." €");

                    if ($notify) {
                        $msg = "⚡️ *¡Price drop for \"{$search->keywords}\"!*\n\n";
                        $msg .= "*{$title}*\n\n";
                        $msg .= "💰 ".number_format($price, 2, ',', '.')." € (before ".number_format($oldPrice, 2, ',', '.')." €)\n";
                        $msg .= "🔗 https://es.wallapop.com/item/{$urlSlug}";

                        $this->telegram->sendMessage($msg);
                        Logger::info("Price drop: {$wallapopId} - {$title}");
                    }
                }
            }
        } catch (Throwable $e) {
            Error::report($e);
            Logger::info("Error in search '{$search->keywords}': ".$e->getMessage());
        }
    }
}
