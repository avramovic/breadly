<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBreadController as BaseVoyagerBreadController;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;

class VoyagerBreadController extends BaseVoyagerBreadController
{

    public function store(Request $request)
    {
        $result = parent::store($request);

        try {
            $dataType                = Voyager::model('DataType')->orderBy('id', 'desc')->first();
            $dataType->public_browse = isset($request->public_browse);
            $dataType->public_read   = isset($request->public_read);
            $dataType->public_add    = isset($request->public_add);
            $dataType->save();
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return $result;
    }

    public function update(Request $request, $id)
    {
        $result = parent::update($request, $id);

        try {
            $dataType                = Voyager::model('DataType')->find($id);
            $dataType->public_browse = isset($request->public_browse);
            $dataType->public_read   = isset($request->public_read);
            $dataType->public_add    = isset($request->public_add);
            $dataType->save();
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return $result;
    }

    public function destroy($id)
    {
        $dataType = DataType::where('id', $id)->first();
        if ($dataType) {
            Permission::where('table_name', $dataType->name)->delete();
        }

        return parent::destroy($id);
    }
}
