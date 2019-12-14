<?php namespace App\Http\Controllers;

use App\Http\Requests\TinyWebDbGetValueRequest;
use App\Http\Requests\TinyWebDbStoreAValueRequest;
use Carbon\Carbon;

class TinyWebDbController extends Controller
{

    public function getvalue(TinyWebDbGetValueRequest $request)
    {
        $data = \DB::table('tinywebdb')
            ->where('tag', $request->tag)
            ->first();

        $value = $data ? $data->value : '';

        return $this->response('VALUE', $request->tag, $value);
    }

    public function storeavalue(TinyWebDbStoreAValueRequest $request)
    {
        $data = \DB::table('tinywebdb')
            ->where('tag', $request->tag)
            ->first();

        $now = Carbon::now()->toDateTimeString();

        if ($data) {
            \DB::table('tinywebdb')
                ->where('tag', $request->tag)
                ->update([
                    'value'      => $request->value,
                    'updated_at' => $now,
                ]);
        } else {
            \DB::table('tinywebdb')
                ->insert([
                    'tag'        => $request->tag,
                    'value'      => $request->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        return $this->response('STORED', $request->tag, $request->value);
    }

    protected function response($action, $tag, $value = '')
    {
        return [$action, $tag, $value];

    }
}