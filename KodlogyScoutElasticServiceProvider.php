<?php
/**
 * Created by PhpStorm.
 * User: orange
 * Date: 14.09.2018
 * Time: 12:27
 */

namespace Kodlogy\ScoutElastic;

use InvalidArgumentException;
use Laravel\Scout\EngineManager;
use Kodlogy\ScoutElastic\Console\ScoutIndexCommand;
use ScoutElastic\Console\ElasticMigrateCommand;
use ScoutElastic\Console\ElasticUpdateMappingCommand;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
use ScoutElastic\Console\SearchableModelMakeCommand;
use ScoutElastic\Console\SearchRuleMakeCommand;
use ScoutElastic\Console\IndexConfiguratorMakeCommand;
use ScoutElastic\ScoutElasticServiceProvider;

class KodlogyScoutElasticServiceProvider extends ScoutElasticServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/scout_elastic.php' => config_path('scout_elastic.php'),
        ]);

        $this->commands([
            // make commands
            IndexConfiguratorMakeCommand::class,
            SearchableModelMakeCommand::class,
            SearchRuleMakeCommand::class,

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexUpdateCommand::class,
            ElasticIndexDropCommand::class,
            ElasticUpdateMappingCommand::class,
            ElasticMigrateCommand::class,

            //kodlogy commands
            ScoutIndexCommand::class,

        ]);

        $this->app->make(EngineManager::class)
            ->extend('elastic', function () {
                $indexerType = config('scout_elastic.indexer', 'single');
                $updateMapping = config('scout_elastic.update_mapping', true);

                $indexerClass = '\\ScoutElastic\\Indexers\\'.ucfirst($indexerType).'Indexer';

                if (!class_exists($indexerClass)) {
                    throw new InvalidArgumentException(sprintf(
                        'The %s indexer doesn\'t exist.',
                        $indexerType
                    ));
                }

                return new KodlogyElasticEngine(new $indexerClass(), $updateMapping);
            });
    }
}