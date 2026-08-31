<?php
namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait RecordsSyncOutbox
{
    public static function bootRecordsSyncOutbox()
    {
        static::creating(function ($model) {
            if (empty($model->sync_uuid)) {
                $model->sync_uuid = (string) Str::uuid();
            }
        });

        static::created(function ($model) {
            self::recordToOutbox($model, 'INSERT');
        });

        static::updated(function ($model) {
            self::recordToOutbox($model, 'UPDATE');
        });

        static::deleted(function ($model) {
            self::recordToOutbox($model, 'DELETE');
        });
    }

    protected static function recordToOutbox($model, $action)
    {
        DB::table('sync_outboxes')->insert([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'sync_uuid' => $model->sync_uuid,
            'action' => $action,
            'payload' => json_encode($model->toArray()),
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}