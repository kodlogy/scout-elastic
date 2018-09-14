<?php
/**
 * Created by PhpStorm.
 * User: orange
 * Date: 14.09.2018
 * Time: 12:21
 */

namespace Kodlogy\ScoutElastic;


use ScoutElastic\ElasticEngine;
use Illuminate\Database\Eloquent\Collection;

class KodlogyElasticEngine extends ElasticEngine
{

    public function map($results, $model)
    {
        if ($this->getTotalCount($results) == 0) {
            return Collection::make();
        }

        $ids = $this->mapIds($results);

        $modelKey = $model->getKeyName();

        $models = $model->whereIn($modelKey, $ids)
            ->get()
            ->keyBy($modelKey);
        $elements =[];
        foreach ($results['hits']['hits'] as $item) {
            $source = $item['_source'];
            $source["id"] = $item['_id'];
            $elements[]= $source;
        }
        $collection =Collection::make($elements);
        return $collection;
    }
}