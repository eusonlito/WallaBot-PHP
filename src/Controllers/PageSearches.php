<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Search;
use App\Models\Item;

class PageSearches extends ControllerAbstract
{
    public function handle(): void
    {
        $searches = Search::findAll();
        $counts = Item::countBySearch();

        $page = 'searches';

        require dirname(__DIR__) . '/Views/layout.php';
    }
}
