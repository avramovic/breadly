<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerDatabaseController as BaseVoyagerDatabaseController;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;

class VoyagerDatabaseController extends BaseVoyagerDatabaseController
{

    public function storeBread(Request $request)
    {
        Voyager::canOrFail('browse_database');

        $result = parent::storeBread($request);

        try {
            $dataType                     = Voyager::model('DataType')->orderBy('id', 'desc')->first();
            $dataType->public_browse_read = isset($request->public_browse_read);
            $dataType->public_add         = isset($request->public_add);
            $dataType->save();
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return $result;
    }

    public function updateBread(Request $request, $id)
    {
        Voyager::canOrFail('browse_database');

        $result = parent::updateBread($request, $id);

        try {
            $dataType                     = Voyager::model('DataType')->find($id);
            $dataType->public_browse_read = isset($request->public_browse_read);
            $dataType->public_add         = isset($request->public_add);
            $dataType->save();
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return $result;
    }

    public function deleteBread($id)
    {
        Voyager::canOrFail('browse_database');

        $dataType = DataType::where('id', $id)->first();
        if ($dataType) {
            Permission::where('table_name', $dataType->name)->delete();
        }

        return parent::deleteBread($id);
    }
}
