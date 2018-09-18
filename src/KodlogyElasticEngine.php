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

    public function get(Builder $builder)
    {

        $collection = Collection::make($this->map(
            $this->search($builder), $builder->model
        ));

        if (isset($builder->with) && $collection->count() > 0) {
            $collection->load($builder->with);
        }

        return $collection;
    }


    public function buildSearchQueryPayloadCollection(Builder $builder, array $options = [])
    {
        $payloadCollection = collect();
        if ($builder instanceof SearchBuilder) {


            $searchRules = $builder->rules ?: $builder->model->getSearchRules();

            foreach ($searchRules as $rule) {
                if (is_callable($rule)) {
                    $queryPayload = call_user_func($rule, $builder);
                } else {
                    /** @var SearchRule $ruleEntity */
                    $ruleEntity = new $rule($builder);

                    if ($ruleEntity->isApplicable()) {
                        $queryPayload = $ruleEntity->buildQueryPayload();
                    } else {
                        continue;
                    }
                }
                $payload = $this->buildSearchQueryPayload(
                    $builder,
                    $queryPayload,
                    $options
                );
                $payloadCollection->push($payload);
            }
        } else {
            $payload = $this->buildSearchQueryPayload(
                $builder,
                ['must' => ['match_all' => new \stdClass()]],
                $options
            );

            $payloadCollection->push($payload);
        }

        return $payloadCollection;
    }
}