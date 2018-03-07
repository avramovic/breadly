<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BreadlyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->createMissingBreadModelClasses();
    }


    /**
     * This will create missing BREAD models in-memory, so we don't
     * have to write classes to file-system, making it easier
     * to scale between multiple servers.
     */
    protected function createMissingBreadModelClasses()
    {
        $tables = collect(array_map('reset', \DB::select('SHOW TABLES')))
            ->diff(array_merge(config('voyager.database.tables.hidden'), ['users', 'roles']));

        foreach ($tables as $table) {
            $model     = ucfirst(strtolower(Str::singular($table)));
            $namespace = rtrim(config('voyager.models.namespace', 'App\\Models\\'), '\\');

//            if (!is_file(base_path('app/Models/'.$model.'.php'))) {
            if (!class_exists($namespace.'\\'.$model)) {
                $newClass = <<<CLASS
namespace {$namespace};

class {$model} extends BreadModel {

}
CLASS;

                eval($newClass);
            }
        }
    }
}
