<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Search;
use App\Services\SearchSync;
use App\Services\Wallapop\Client as WallapopClient;
use Throwable;

class PageGeneralSearch extends ControllerAbstract
{
    public function handle(): void
    {
        $keywords = $this->getInputString('keywords');
        $items = [];
        $search = null;
        $searchError = null;

        if (!empty($keywords)) {
            $search = new Search($this->searchData() + [
                'id' => 0,
                'created_at' => '',
                'updated_at' => '',
            ]);

            try {
                $items = (new SearchSync())->filterItems($search, (new WallapopClient())->search($search));
            } catch (Throwable) {
                $searchError = 'No se pudo completar la búsqueda. Inténtalo de nuevo en unos instantes.';
            }
        }

        $page = 'general-search';
        require dirname(__DIR__).'/Views/layout.php';
    }
}
