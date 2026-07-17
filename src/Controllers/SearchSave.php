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

        $extra = $this->getInput('extra_filters');

        if (is_array($extra)) {
            foreach ($extra as $key => $value) {
                if (is_array($value)) {
                    $extra[$key] = implode(',', array_filter($value, fn($v) => $v !== ''));
                }
            }

            $extra = array_filter($extra, fn($v) => $v !== '' && $v !== null);
        }

        $data = [
            'keywords' => $keywords,
            'category_ids' => $this->getInputString('category_ids'),
            'price_min' => $this->getInputFloat('price_min'),
            'price_max' => $this->getInputFloat('price_max'),
            'distance' => $this->getInputString('distance'),
            'latitude' => $this->getInputFloat('latitude'),
            'longitude' => $this->getInputFloat('longitude'),
            'is_shippable' => $this->getInputBool('is_shippable') ? 1 : ($this->getInput('is_shippable') === null ? null : 0),
            'extra_filters' => !empty($extra) ? json_encode($extra, JSON_UNESCAPED_SLASHES) : null,
            'active' => $this->getInput('active') !== null ? 1 : 0,
            'title_only' => $this->getInput('title_only') !== null ? 1 : 0,
        ];

        if ($id) {
            $search = Search::findOrFail($id)->update($data);

            new SearchSync()->sync($search, false);

            $this->redirect('/searches');
        }

        new SearchSync()->sync(Search::create($data), false);

        $this->redirect('/searches');
    }
}
