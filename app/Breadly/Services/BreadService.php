<?php namespace App\Breadly\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use TCG\Voyager\Models\DataType;

class BreadService
{
    public $table = null;
    protected $columns = [];
    protected $hiddenTables = [];

    public function __construct($table)
    {
        $this->setTable($table);
        $this->hiddenTables = array_merge($this->hiddenTables, config('voyager.database.tables.hidden'));
    }


    public function setTable($table)
    {
        if (!\Schema::hasTable($table) || $this->isHiddenTable($table)) {
            throw new ModelNotFoundException('Unknown data source: '.$table);
        }

        $this->table   = $table;
        $this->columns = \Schema::getColumnListing($this->table);
    }


    public function getDataType()
    {
        return DataType::where('name', $this->table)->firstOrFail();
    }

    public function getDataTypeColumns()
    {
        return $this->getDataType()->rows;
    }

    public function getDataTypeColumn($name)
    {
        return $this->getDataTypeColumns()->filter(function ($item) use ($name) {
            return strtolower($item->field) == strtolower($name);
        })->first();
    }


    public function isHiddenTable($table = null)
    {
        return in_array($table ?: $this->table, $this->hiddenTables);
    }


    public function applyHttpScopes(&$query, Request $request, $paginate = false)
    {
        $query->where(function () use ($query, $request) {

            foreach (with(collect($request->query()))->except(['bindings', 'withDeleted', 'deletedOnly', 'with', 'page', 'perPage']) as $command => $params) {
                $command = strtolower($command);

                if ($command == 'where') {
                    foreach ($params as $field => $value) {
                        list($operator, $value) = $this->parseSqlValue($value);
                        $query->where($field, $operator, $value);
                    }
                } elseif ($command == 'orwhere') {
                    foreach ($params as $field => $value) {
                        list($operator, $value) = $this->parseSqlValue($value);
                        $query->orWhere($field, $operator, $value);
                    }
                } elseif ($command == 'wherein') {
                    foreach ($params as $field => $value) {
                        $value = explode(',', $value);
                        $query->whereIn($field, $value);
                    }
                } elseif ($command == 'orwherein') {
                    foreach ($params as $field => $value) {
                        $value = explode(',', $value);
                        $query->orWhereIn($field, $value);
                    }
                } elseif ($command == 'wherenotin') {
                    foreach ($params as $field => $value) {
                        $value = explode(',', $value);
                        $query->whereNotIn($field, $value);
                    }
                } elseif ($command == 'orwherenotin') {
                    foreach ($params as $field => $value) {
                        $value = explode(',', $value);
                        $query->orWhereNotIn($field, $value);
                    }
                } elseif ($command == 'wherenull') {
                    foreach ($params as $field) {
                        $query->whereNull($field);
                    }
                } elseif ($command == 'wherenotnull') {
                    foreach ($params as $field) {
                        $query->whereNotNull($field);
                    }
                } elseif ($command == 'orwherenull') {
                    foreach ($params as $field) {
                        $query->orWhereNull($field);
                    }
                } elseif ($command == 'orwherenotnull') {
                    foreach ($params as $field) {
                        $query->orWhereNotNull($field);
                    }
                } elseif ($command == 'orderby') {
                    foreach ($params as $field => $order) {
                        $order = ($order == 'desc') ? 'desc' : 'asc';
                        $query->orderBy($field, $order);
                    }
                } elseif ($command == 'take') {
                    $query->take((int)$params);
                } elseif ($command == 'skip') {
                    $query->skip((int)$params);
                } elseif ($command == 'whereRaw') {
                    $query->whereRaw($params, isset($request->bindings) ? explode(',', $request->bindings) : []);
                } else {
                    throw new \InvalidArgumentException("Invalid command: ".$command);
                }
            }

        });

        if ($paginate) {
            $query->paginate(isset($request->perPage) ? (int)$request->perPage : setting('site.perPage', 20));
        }

        return $query;
    }

    public function applySoftDeleteChecks(&$query, $withDeleted = false, $deletedOnly = false)
    {
        if ($this->hasColumn('deleted_at')) {
            if (!$withDeleted) {
                $query->whereNull($this->table.'.deleted_at');
            }

            if ($deletedOnly) {
                $query->whereNotNull($this->table.'.deleted_at');
            }
        }
    }

    protected function parseSqlValue($value)
    {
        $split = explode(',', $value);

        if (count($split) <= 1) {
            return ['=', $value];
        }

        $operator = array_shift($split);

        if (!in_array($operator, ['=', '<', '>', '<=', '>=', 'in', 'between', 'is', 'like'])) {
            return ['=', $value];
        }

        return [$operator, implode(',', $split)];
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn($column)
    {
        return in_array($column, $this->getColumns());
    }

    public function can($action, $user = null)
    {
        if (!$user) {
            if (in_array($action, ['browse', 'read'])) {
                return (bool)$this->getDataType()->public_browse_read;
            } elseif ($action == 'add') {
                return (bool)$this->getDataType()->public_add;
            } else {
                return false;
            }
        } else {
            return $user->hasPermission($action.'_'.$this->table);
        }
    }

    public function getOptions($column)
    {
        if (!$this->hasColumn($column)) {
            return null;
        }

        $row = $this->getDataTypeColumn($column);

        if (!$row) {
            return null;
        }

        return json_decode($row->details, true);
    }

    public function getOption($option, $column)
    {
        if ($options = $this->getOptions($column)) {
            return isset($options[$option]) ? $options[$option] : null;
        }

        return null;
    }

    public function getOptionByAction($action, $option, $column)
    {
        $option = $this->getOption($option, $column);

        if (!$option) {
            return null;
        }

        if (!is_array($option)) {
            return $option;
        }

        if (isset($option[$action])) {
            return $option[$action];
        }

        return $option;
    }

    public function getReadableColumns($action, $user = null)
    {
        $columns       = $this->getColumns();
        $hiddenColumns = [];

        foreach ($columns as $column) {
            $hidden = $this->getOptionByAction($action, 'hidden', $column);

            if (is_array($hidden)) {
                $hidden = isset($hidden[$user ? 'user' : 'anonymous']) ? $hidden[$user ? 'user' : 'anonymous'] : false;
            }

            if ($hidden === true) {
                $hiddenColumns[] = $column;
            }
        }

        if (count($hiddenColumns) > 0) {
            $columns = array_diff($columns, $hiddenColumns);
        }

        if ($this->table == 'users') {
            $columns = array_filter($columns, function ($column) {
                return !in_array($column, ['password', 'verification_token', 'remember_token']);
            });
        }

        return $columns;
    }

    public function processSelects($columns = null, $asAliases = false)
    {
        if (!$columns) {
            $columns = $this->getColumns();
        }

        $selects = [];

        foreach ($columns as $column) {
            $selects[] = "{$this->table}.{$column}".($asAliases ? " AS {$this->table}_{$column}" : '');
        }

        return $selects;
    }

    public function join(&$query, $otherTable, $action, $localId = null, $user = null)
    {
        if (!$localId) {
            $localId = str_singular($otherTable).'_id';
        }
        $query->leftJoin($otherTable, "{$this->table}.{$localId}", '=', "{$otherTable}.id");

        $subService = new static($otherTable);
        $columns    = $subService->getReadableColumns($action, $user);
        $columns    = $subService->processSelects($columns, true);

        foreach ($columns as $column) {
            $query->addSelect($column);
        }

        $subService->applySoftDeleteChecks($query);
    }

    public function makeValidation($action)
    {
        $columns         = $this->getColumns();
        $validationRules = [];
        $request         = app('request');

        foreach ($columns as $column) {
            $validation = $this->getOptionByAction($action, 'validation', $column);

            if ($validation) {
                $validationRules[$column] = str_replace(':id', $request->id, $validation);
            }
        }

        return $validationRules;
    }

    public function getUploadOptions($action, $column, $user = null)
    {
        $uploadOptions = $this->getOptionByAction($action, 'upload', $column);

        if (is_array($uploadOptions)) {
            $uploadOptions = isset($uploadOptions[$user ? 'user' : 'anonymous']) ? $uploadOptions[$user ? 'user' : 'anonymous'] : $uploadOptions;
        }

        return $uploadOptions;
    }

    public function getUploadColumns()
    {
        $columns       = $this->getColumns();
        $uploadColumns = [];

        foreach ($columns as $column) {
            if ($this->getOption('upload', $column)) {
                $uploadColumns[] = $column;
            }
        }

        return $uploadColumns;
    }

    public function getDefaultColumnValue($column)
    {
        $query = \DB::table('information_schema.columns')
            ->select(['column_default'])
            ->where('table_name', $this->table)
            ->where('column_name', $column)
            ->first();

        if (!$query) {
            return null;
        }

        $postgre = config('database.default', 'mysql') == 'pgsql';

        if (!$postgre) {
            return $query->column_default;
        }

        $elements = explode('::', $query->column_default);

        return trim(array_shift($elements), "'");
    }

    public function isDefaultColumnValue($column, $value)
    {
        return $value == $this->getDefaultColumnValue($column);
    }


}