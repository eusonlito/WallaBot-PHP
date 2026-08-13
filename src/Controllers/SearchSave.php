<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Search;
use App\Services\SearchSync;

class SearchSave extends ControllerAbstract
{
    public function handle(): void
    {
        $id = $this->getInputInt('id');
        $keywords = $this->getInputString('keywords');

        if (empty($keywords)) {
            $this->response(['error' => 'Keywords are required'], 400);
        }

        $data = $this->searchData(true);

        if ($id) {
            $search = Search::findOrFail($id)->update($data);

            new SearchSync()->sync($search, false, true);

            $this->redirect('/searches');
        }

        new SearchSync()->sync(Search::create($data), false);

        $this->redirect('/searches');
    }
}
