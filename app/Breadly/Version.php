<?php namespace App\Breadly;

class Version {
    public static function get()
    {
        if (is_file(base_path('.version'))) {
            $version = trim(file_get_contents(base_path('.version')));
            if (!empty($version)) {
                return $version;
            }
        }

        return config('app.version', '?');
    }
}