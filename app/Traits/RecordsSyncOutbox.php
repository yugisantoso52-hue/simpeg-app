<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait RecordsSyncOutbox
{
    public static function bootRecordsSyncOutbox()
    {
        static::creating(function ($model) {
            if (empty($model->sync_uuid)) {
                $model->sync_uuid = (string) Str::uuid();
            }
        });

        static::updating(function ($model) {
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
        $uuid = $model->sync_uuid;

        // Jika sync_uuid masih null (misal hasil impor lama), buatkan otomatis
        if (empty($uuid)) {
            $uuid = (string) Str::uuid();
            $model->sync_uuid = $uuid;

            if ($model->id) {
                try {
                    DB::table($model->getTable())
                        ->where('id', $model->id)
                        ->update(['sync_uuid' => $uuid]);
                } catch (\Throwable $e) {
                    // Ignore jika tabel tidak memiliki kolom sync_uuid
                }
            }
        }

        try {
            DB::table('sync_outboxes')->insert([
                'table_name' => $model->getTable(),
                'record_id'  => $model->id,
                'sync_uuid'  => $uuid,
                'action'     => $action,
                'payload'    => json_encode($model->getAttributes()),
                'status'     => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai kendala pada outbox menggagalkan aksi simpan data pegawai
            Log::warning("Gagal mencatat sync_outbox untuk {$model->getTable()} ID {$model->id}: " . $e->getMessage());
        }
    }
}