<?php namespace App\Breadly\Components;

use Caffeinated\Modules\Exceptions\ModuleNotFoundException;
use Caffeinated\Modules\Repositories\LocalRepository;
use Illuminate\Support\Collection;

class DatabaseModuleRepository extends LocalRepository
{

    public function all()
    {
        return $this->getCache()->sortBy('order')->map(function ($module) {
            $module            = (array)$module;
            $module['enabled'] = (bool)$module['enabled'];
            return $module;
        });
    }

    public function set($property, $value)
    {
        list($slug, $key) = explode('::', $property);

        return \DB::table('modules')
            ->where('slug', $slug)
            ->update([$key => $value]);
    }

    public function where($key, $value)
    {
        $module = $this->all()->where($key, $value)->first();

        if (!$module) {
            $this->optimize();
            $module = $this->all()->where($key, $value)->first();
        }

        if (!$module) {
            throw new ModuleNotFoundException($value);
        }

        return collect($module);
    }

    public function optimize()
    {
        $cache     = $this->getCache();
        $basenames = $this->getAllBasenames();
        $modules   = collect();

        $basenames->each(function ($module, $key) use ($modules, $cache) {
            $basename = collect(['basename' => $module]);
            $temp     = $basename->merge(collect($cache->where('basename', $module)->first() ? (array)$cache->where('basename', $module)->first() : []));
            $manifest = $temp->merge(collect($this->getManifest($module)));

            $modules->put($module, $manifest);
        });

        \DB::table('modules')
            ->whereNotIn('basename', $basenames->toArray())
            ->delete();

        $modules->each(function ($module) {

            $module->put('crc32', crc32($module->get('slug')));

            if (!$module->has('enabled')) {
                $module->put('enabled', config('modules.enabled', true));
            }

            if (!$module->has('order')) {
                $module->put('order', 9001);
            }

            $exists = (bool)\DB::table('modules')
                    ->where([
                        'basename' => $module->get('basename'),
                    ])
                    ->count() > 0;

            if ($exists) {
                \DB::table('modules')
                    ->where([
                        'basename' => $module->get('basename'),
                    ])
                    ->update((array)$module->except(['id'])->all());
            } else {
                \DB::table('modules')
                    ->insert((array)$module->all());
            }

            return $module;
        });

        return true;
    }

    private function getCache()
    {
        return \Schema::hasTable('modules') ? \DB::table('modules')->get()->toBase() : new Collection();
    }
}