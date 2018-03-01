<?php namespace App\Http\Controllers\Api;

use App\Breadly\Services\BreadService;
use App\Events\BreadDataAdded;
use App\Events\BreadDataDeleted;
use App\Events\BreadDataEdited;
use App\Events\BreadFileUploaded;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use Mimey\MimeTypes;
use Ramsey\Uuid\Uuid;

class BreadController extends ApiController
{

    public function browse($table, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        //create query builder
        $query = \DB::table($table);

        //check if user (even anonymous) can browse
        $canBrowse = $breadService->can(static::ACTION_BROWSE, $this->user);

        if (!$canBrowse) {
            if (!$this->user) {
                return $this->response('Not allowed to browse '.$table, 401);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to browse '.$table, 403);
            }
        }

        //select readable columns
        $select = $breadService->getReadableColumns(static::ACTION_BROWSE, $this->user);
        $query->select($breadService->processSelects($select));

        $customHeaders = [];

        if (isset($request->page) || isset($request->perPage)) {
            $countQuery = clone $query;
            $page       = (int)$request->page ?: 1;
            $perPage    = (int)$request->perPage ?: 20;

            $breadService->applyHttpScopes($countQuery, $request, false);
            $breadService->applySoftDeleteChecks($countQuery, $request->withDeleted, $request->deletedOnly);

            $breadService->applyHttpScopes($query, $request, true);
            $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);

            $total      = $countQuery->count();
            $totalPages = (int)ceil($total / $perPage);

            $customHeaders['X-Pagination-CurrentPage'] = $page;
            $customHeaders['X-Pagination-PerPage']     = $perPage;
            $customHeaders['X-Pagination-Total']       = $total;
            $customHeaders['X-Pagination-TotalPages']  = $totalPages;
        } else {
            $breadService->applyHttpScopes($query, $request, false);
            $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);
        }

        //process joins
        if (isset($request->with)) {
            if (!is_array($request->with)) {
                $joins         = explode(',', $request->with);
                $request->with = [];

                foreach ($joins as $join) {
                    $request->with[$join] = null;
                }
            }

            foreach ($request->with as $otherTable => $field) {
                $breadService->join($query, $otherTable, static::ACTION_BROWSE, $field, $this->user);
            }
        }

        return $this->response($breadService->format($query->get()), 200, $customHeaders);
    }

    public function read($table, $id, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        //create query builder
        $query = \DB::table($table);

        //check if user (even anonymous) can read
        $canRead = $breadService->can(static::ACTION_READ, $this->user);

        if (!$canRead) {
            if (!$this->user) {
                return $this->response('Not allowed to read '.$table, 401);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to read '.$table, 403);
            }
        }

        //select readable columns
        $select = $breadService->getReadableColumns(static::ACTION_READ, $this->user);
        $query->select($breadService->processSelects($select));

        //process joins
        if (isset($request->with)) {
            if (!is_array($request->with)) {
                $joins         = explode(',', $request->with);
                $request->with = [];

                foreach ($joins as $join) {
                    $request->with[$join] = false;
                }
            }

            foreach ($request->with as $otherTable => $field) {
                $breadService->join($query, $otherTable, static::ACTION_READ, $field, $this->user);
            }
        }

        if ($breadService->hasColumn('guid')) {
            $query->where($table.'guid', $id);
        } else {
            $query->where($table.'.id', (int)$id);
        }

        $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);

        return $this->response($breadService->format($query->first()));
    }

    public function add($table, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        //check if user (even anonymous) can add
        $canAdd = $breadService->can(static::ACTION_ADD, $this->user);

        if (!$canAdd) {
            if (!$this->user) {
                return $this->response('Not allowed to add '.$table, 401);
            } else {
                return $this->response('Not allowed to add '.$table, 403);
            }
        }

        //validate request
        $validationRules = $breadService->makeValidation(static::ACTION_ADD);
        if (count($validationRules) > 0) {
            $request->validate($validationRules);
        }

        //clean up data
        $data = $request->request->all();

        if (isset($data['deleted_at'])) {
            unset($data['deleted_at']);
        }

        if ($breadService->hasColumn('created_at')) {
            $data['created_at'] = Carbon::now();
        }
        if ($breadService->hasColumn('updated_at')) {
            $data['updated_at'] = Carbon::now();
        }
        if ($breadService->hasColumn('user_id') && $this->user) {
            $data['user_id'] = $this->user->id;
        }
        if ($breadService->hasColumn('guid')) {
            $data['guid'] = Uuid::uuid4()->toString();
        }

        $newRecordId = \DB::table($table)->insertGetId($data);

        event(new BreadDataAdded($table, $newRecordId, $data, $this->user));

        return $this->response($newRecordId);
    }

    public function edit($table, $id = null, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        $query = \DB::table($table);

        //check if user can edit
        $canEdit = $breadService->can(static::ACTION_EDIT, $this->user);

        if (!$canEdit) {
            if (!$this->user) {
                return $this->response('Not allowed to edit '.$table, 401);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to edit '.$table, 403);
            }
        }

        //validate request
        $validationRules = $breadService->makeValidation(static::ACTION_EDIT);
        if (count($validationRules) > 0) {
            $request->validate($validationRules);
        }

        $data = $request->request->all();

        if (isset($data['deleted_at'])) {
            unset($data['deleted_at']);
        }

        if (isset($data['id'])) {
            unset($data['id']);
        }

        //timestamps?
        if ($breadService->hasColumn('updated_at')) {
            $data['updated_at'] = Carbon::now();
        }

        if ($id) {
            if ($breadService->hasColumn('guid')) {
                $query->where($table.'guid', $id);
            } else {
                $query->where($table.'.id', (int)$id);
            }
        } else {
            if (empty($request->query())) {
                return $this->response("You must specify ID or query scope!", 422);
            } else {
                $breadService->applyHttpScopes($query, $request);
            }
        }
        $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);


        $toUpdate = $query->get();
        $ids      = [];

        foreach ($toUpdate as $row) {
            $ids[] = $row->id;

            event(new BreadDataEdited($table, $row->id, $row, $data, $this->user));
        }

        $updatedCount = \DB::table($table)->whereIn('id', $ids)->update($data);
        return $this->response($updatedCount, 200);
    }

    public function delete($table, $id = null, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        $query = \DB::table($table);

        //check if user can delete
        $canDelete = $breadService->can(static::ACTION_DELETE, $this->user);

        if (!$canDelete) {
            if (!$this->user) {
                return $this->response('Not allowed to delete '.$table, 401);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to delete '.$table, 403);
            }
        }

        if ($id) {
            if ($breadService->hasColumn('guid')) {
                $query->where($table.'guid', $id);
            } else {
                $query->where($table.'.id', (int)$id);
            }
        } else {
            if (empty($request->query())) {
                return $this->response("You must specify ID or query scope!", 422);
            } else {
                $breadService->applyHttpScopes($query, $request);
            }
        }
        $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);

        if ($breadService->hasColumn('deleted_at')) {

            $toDelete = $query->get();
            $ids      = [];

            foreach ($toDelete as $entity) {
                $ids[] = $entity->id;

                event(new BreadDataDeleted($table, $entity, true, $this->user));
            }

            return \DB::table($table)->whereIn('id', $ids)->update(['deleted_at' => Carbon::now()]);
        }

        return $this->forceDelete($table, $id, $request);
    }

    public function forceDelete($table, $id = null, Request $request)
    {
        $breadService = new BreadService($table);

        //check if table is hidden
        if ($breadService->isHiddenTable()) {
            $this->response('Unknown data source: '.$table, 404);
        }

        $query = \DB::table($table);

        //check if user can delete
        $canDelete = $breadService->can(static::ACTION_DELETE, $this->user);

        if (!$canDelete) {
            if (!$this->user) {
                return $this->response('Not allowed to delete '.$table, 401);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to delete '.$table, 403);
            }
        }

        if ($id) {
            if ($breadService->hasColumn('guid')) {
                $query->where($table.'guid', $id);
            } else {
                $query->where($table.'.id', (int)$id);
            }
        } else {
            if (empty($request->query())) {
                return $this->response("You must specify ID or query scope!", 422);
            } else {
                $breadService->applyHttpScopes($query, $request);
            }
        }
        $breadService->applySoftDeleteChecks($query, $request->withDeleted, $request->deletedOnly);

        //delete uploaded files
        $toDelete    = $query->get();
        $ids         = [];
        $storageDisk = setting('site.upload_disk', 'public');


        foreach ($toDelete as $entity) {
            $ids[] = $entity->id;

            event(new BreadDataDeleted($table, $entity, false, $this->user));

            foreach ($entity as $field => $value) {
                if (in_array($field, $breadService->getUploadColumns())
                    && !empty($value)
                    && \Storage::disk($storageDisk)->exists($value)
                    && !$breadService->isDefaultColumnValue($field, $value)) {
                    \Storage::disk($storageDisk)->delete($value);
                }
            }
        }

        return $this->response(\DB::table($table)->whereIn('id', $ids)->delete());
    }

    public function upload($table, $field, $id, Request $request)
    {
        $breadService = new BreadService($table);

        if (!$breadService->hasColumn($field)) {
            return $this->response('Field does not exist: '.$field, 404);
        }

        $query = \DB::table($table);

        if ($breadService->hasColumn('guid')) {
            $query->where('guid', $id);
        } else {
            $query->where('id', (int)$id);
        }

        $entry = $query->first();

        if (!$entry) {
            return $this->response('Row '.$id.' does not exist in '.$table, 404);
        }

        if (($breadService->hasColumn('created_at') && $breadService->hasColumn('updated_at') && $entry->created_at != $entry->updated_at) || !empty($entry->{$field})) {
            $action = static::ACTION_EDIT;
        } else {
            $action = static::ACTION_ADD;
        }

        //create update query
        $query = \DB::table($table);

        if ($breadService->hasColumn('guid')) {
            $query->where($table.'.guid', $id);
        } else {
            $query->where($table.'.id', (int)$id);
        }

        //check if user can delete
        $canUpload = $breadService->can($action, $this->user);

        if (!$canUpload) {
            if (!$this->user) {
                return $this->response('Not allowed to upload to '.$table, 401);
            } elseif ($table == 'users') {
                $query->where($table.'.id', $this->user->id);
            } elseif ($breadService->hasColumn('user_id')) {
                $query->where($table.'.user_id', $this->user->id);
            } else {
                return $this->response('Not allowed to upload to '.$table, 403);
            }
        }

        $toUpdate = $query->first();

        if (!$toUpdate) {
            return $this->response('Not allowed to upload to update row '.$id, 403);
        }

        //get upload options
        $uploadOptions = $breadService->getUploadOptions($action, $field, $this->user);

        if (!$uploadOptions) {
            return $this->response('Not allowed to upload '.$field, 403);
        }

        //get uploaded file
        $uploadedFile = file_get_contents('php://input');

        //check mime type
        if (isset($uploadOptions['type'])) {
            if ($uploadOptions['type'] == 'image') {
                $uploadOptions['type'] = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ];
            } elseif (is_string($uploadOptions['type'])) {
                $uploadOptions['type'] = explode('|', $uploadOptions['type']);
            }
        }

        //get mime type
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->buffer($uploadedFile);

        if (isset($uploadOptions['type']) && is_array($uploadOptions['type']) && !in_array($mimeType, $uploadOptions['type'])) {
            return $this->response('Not allowed to upload '.$mimeType, 422);
        }

        $fileName = $request->query('fileName');

        if ($fileName) {
            $basename  = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        } else {
            $basename  = str_singular($table).'-'.$id.'-'.$field;
            $extension = null;
        }

        $extension   = $extension ?: with(new MimeTypes())->getExtension($mimeType);
        $filePath    = $table.'/'.Carbon::now()->format('FY').'/'.$basename.'.'.$extension;
        $storageDisk = setting('site.upload_disk', 'public');

        if (in_array($extension, ['php', 'php3', 'php4', 'php5', 'php7', 'phar', 'asp', 'aspx'])) {
            return $this->response('Not allowed to upload '.$extension, 422);
        }

        if (\Storage::disk($storageDisk)->exists($filePath)) {
            $filePath = $table.'/'.Carbon::now()->format('FY').'/'.$basename.'-'.str_random(10).'.'.$extension;
        }

        //check filesize
        $size = strlen($uploadedFile) / 1024;

        if (isset($uploadOptions['validation']) && isset($uploadOptions['validation']['maxFileSize'])) {
            if ($size > $uploadOptions['validation']['maxFileSize']) {
                return $this->response('File size can not be greater than '.$uploadOptions['validation']['maxFileSize'].' kilobytes', 422);
            }
        }

        $isImage = strpos($mimeType, 'image') === 0;

        //if image
        if ($isImage) {
            /** @var Image $image */
            $image  = \Image::make($uploadedFile);
            $width  = $image->width();
            $height = $image->height();

            if (isset($uploadOptions['validation'])) {
                if (isset($uploadOptions['validation']['maxWidth']) && $width > $uploadOptions['validation']['maxWidth']) {
                    return $this->response('Image width must be less than '.$uploadOptions['validation']['maxWidth'].' pixels', 422);
                }

                if (isset($uploadOptions['validation']['maxHeight']) && $width > $uploadOptions['validation']['maxHeight']) {
                    return $this->response('Image height must be less than '.$uploadOptions['validation']['maxHeight'].' pixels', 422);
                }

                if (isset($uploadOptions['validation']['minWidth']) && $height < $uploadOptions['validation']['minWidth']) {
                    return $this->response('Image width must be greater than '.$uploadOptions['validation']['minWidth'].' pixels', 422);
                }

                if (isset($uploadOptions['validation']['minHeight']) && $height < $uploadOptions['validation']['minHeight']) {
                    return $this->response('Image height must be greater than '.$uploadOptions['validation']['minHeight'].' pixels', 422);
                }
            }

            $shouldResize = isset($uploadOptions['resize']);

            if ($shouldResize) {
                $method    = isset($uploadOptions['resize']['method']) ? $uploadOptions['resize']['method'] : 'fit';
                $newWidth  = isset($uploadOptions['resize']['width']) ? $uploadOptions['resize']['width'] : null;
                $newHeight = isset($uploadOptions['resize']['height']) ? $uploadOptions['resize']['height'] : null;

                if ($newWidth && $newHeight) {
                    switch ($method) {
                        case 'resize':
                            $image->resize($newWidth, $newHeight);
                            break;
                        case 'crop':
                            $image->crop($newWidth, $newHeight);
                            break;
                        default:
                            $image->fit($newWidth, $newHeight);
                            break;
                    }
                } elseif ($newWidth) {
                    $image->widen($newWidth);
                } elseif ($newHeight) {
                    $image->heighten($newHeight);
                }
            }

            \Storage::disk($storageDisk)->put($filePath, $image->encode(), 'public');
        } else {
            \Storage::disk($storageDisk)->put($filePath, $uploadedFile, 'public');
        }

        //delete old file (if any)
        if (!empty($toUpdate->{$field})
            && \Storage::disk($storageDisk)->exists($toUpdate->{$field})
            && !$breadService->isDefaultColumnValue($field, $toUpdate->{$field})) {
            \Storage::disk($storageDisk)->delete($toUpdate->{$field});
        }

        \DB::table($table)
            ->where('id', $toUpdate->id)
            ->limit(1)
            ->update([
                $field       => $filePath,
                'updated_at' => Carbon::now(),
            ]);


        event(new BreadFileUploaded($table, $field, $id, base64_encode($uploadedFile), $this->user));

        return $this->response($filePath);
    }
}