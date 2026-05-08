<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Item;
use App\Models\Search;

class PageResults extends ControllerAbstract
{
    public function handle(): void
    {
        $searchId = $this->getInputInt('search_id');
        $sort = $this->getInputString('sort', 'date_desc');
        $favoritesOnly = $this->getInputBool('favorites_only', false);

        $items = Item::findLatest(100, $searchId, $sort, $favoritesOnly);
        $searches = Search::findAll();

        // Calculate stats
        $total = count($items);
        $new = 0;
        $sum = 0;
        $today = 0;

        $now = time();
        $todayStr = date('Y-m-d');

        foreach ($items as $item) {
            $created = strtotime($item->created_at);

            if (($now - $created) < 3600) {
                $new++;
            }

            if (date('Y-m-d', $created) === $todayStr) {
                $today++;
            }

            $sum += $item->price;
        }

        $avgPrice = $total ? round($sum / $total) : 0;

        $activeSearch = null;

        if ($searchId) {
            foreach ($searches as $s) {
                if ($s->id === $searchId) {
                    $activeSearch = $s;
                    break;
                }
            }
        }

        $page = 'results';
        require dirname(__DIR__) . '/Views/layout.php';
    }
}
