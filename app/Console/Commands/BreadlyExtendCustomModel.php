<?php

namespace App\Console\Commands;

use TCG\Voyager\Commands\MakeModelCommand;

class BreadlyExtendCustomModel extends MakeModelCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'breadly:extend:model {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend breadly model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $modelClass = $this->argument('model');
        $modelFile  = base_path().'/app/Models/'.$modelClass.'.php';
        $model      = $this->files->get($modelFile);
        $model      = str_replace('extends Model', 'extends BreadModel', $model);

        $this->files->put($modelFile, $model);

        $this->line('Success!');
    }
}
