<?php

namespace Kodlogy\ScoutElastic\Console;

use App\Models\Account;
use App\Models\Activity;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\DomainCompany;
use App\User;
use Illuminate\Console\Command;

class ScoutIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:index {--import} {--index} {--map}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will index all models...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        ini_set('memory_limit', '2048M');

        $isImportData = $this->option('import');
        $isCreateIndex = $this->option('index');
        $isUpdateMapping = $this->option('map');

        if ($isCreateIndex) {
            $classes = getClassesList(app_path('IndexConfigurators'));
            foreach ($classes as $class) {
                $result = shell_exec("php artisan elastic:create-index " . $class);
                if (str_contains($result, "resource_already_exists_exception")) {
                    $result = shell_exec("php artisan elastic:update-index " . $class);
                    $this->info($class);
                    $this->info($result);

                } else {
                    $this->info($class);
                    $this->info($result);
                }

            }
        }

        if ($isUpdateMapping) {
            $model_classes = getClassesList(app_path('Models'));
            $model_classes[] = "App\\\\User";
            foreach ($model_classes as $model) {
                $this->info($model);
                $result = shell_exec("php artisan elastic:update-mapping " . $model);
                $this->info($result);

            }
        }


        if ($isImportData) {
            $searchable_models = getClassesList(app_path('Models'));
            $searchable_models[] = "App\\\\User";
            foreach ($searchable_models as $model) {
                $result=shell_exec("php artisan scout:import " . $model);
                $this->info($model . " is indexed");
                $this->info($result);
            }
        }


    }
}
