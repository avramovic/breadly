<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TinyWebDbPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tinywebdb:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all data from your Tiny Web DB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Are you sure? This can not be undone!')) {
            \DB::table('tinywebdb')->truncate();
            $this->info('All Tiny Web DB data is removed!');
        } else {
            $this->warn('Chicken! :D');
        }
    }
}
