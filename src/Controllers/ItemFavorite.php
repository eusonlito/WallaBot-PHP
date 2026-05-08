<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Item;

class ItemFavorite extends ControllerAbstract
{
    public function handle(): void
    {
        Item::findOrFail($this->getInputInt('id'))->toggleFavorite();

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
