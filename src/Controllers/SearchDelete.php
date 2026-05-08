<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Search;

class SearchDelete extends ControllerAbstract
{
    public function handle(): void
    {
        Search::delete($this->getInputInt('id'));

        $this->redirect('/searches');
    }
}
