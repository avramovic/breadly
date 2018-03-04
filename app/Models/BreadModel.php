<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class BreadModel extends Eloquent
{

    public function __construct()
    {
        parent::__construct();

        //auto-detect if timestamps are used
        $table            = $this->getTable();
        $columns          = \Schema::getColumnListing($table);
        $this->timestamps = false;

        if (in_array('created_at', $columns) && in_array('updated_at', $columns)) {
            $this->timestamps = true;
        }
    }
}