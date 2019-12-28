<?php namespace App\Breadly\Widgets;

use TCG\Voyager\Widgets\BaseDimmer;

class TinyWebDbWidget extends BaseDimmer
{
    public function run()
    {
        $count = \DB::table('tinywebdb')->count();
        $string = trans_choice('breadly.tinywebdb.entries', $count);

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-data',
            'title'  => "Tiny Web DB: {$count} {$string}",
            'text'   => __('breadly.tinywebdb.explanation'),
            'button' => [
                'text' => url(''),
                'link' => 'javascript:copyTextToClipboard('.json_encode(url('')).');alert("Successfully copied to clipboard!");',
            ],
            'image' => voyager_asset('images/widget-backgrounds/03.jpg'),
        ]));
    }
}