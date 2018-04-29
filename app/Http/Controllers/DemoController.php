<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class DemoController extends Controller
{
    public function resetpass()
    {
        if (env('APP_ENV', 'local') == 'demo') {
            User::find(1)->update(['password' => bcrypt('password')]);

            if (!file_exists(public_path('storage'))) {
                app('files')->link(storage_path('app/public'), public_path('storage'));
            }

            die('Done!');
        }

        die('Not a demo!');
    }

    public function resurrect()
    {
        if (env('APP_ENV', 'local') == 'demo') {
            Artisan::call('migrate:refresh', ['--seed' => true]);

            die('Done!');
        }

        die('Not a demo!');
    }
}
