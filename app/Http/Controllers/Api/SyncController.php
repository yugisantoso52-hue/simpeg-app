<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    protected $modelMap = [
        'pegawai' => \App\Models\Pegawai::class,
        'users' => \App\Models\User::class,
        'riwayat_pendidikan' => \App\Models\RiwayatPendidikan::class,
        'riwayat_jabatan' => \App\Models\RiwayatJabatan::class,
        'riwayat_pangkat' => \App\Models\RiwayatPangkat::class,
        'riwayat_diklat' => \App\Models\RiwayatDiklat::class,
        'mutasi_pegawai' => \App\Models\MutasiPegawai::class,
        'pengajuan_cuti' => \App\Models\PengajuanCuti::class,
        'tugas_belajar' => \App\Models\TugasBelajar::class,
        'riwayat_str_sip' => \App\Models\RiwayatStrSip::class,
        'riwayat_skp' => \App\Models\RiwayatSkp::class,
        'riwayat_penghargaan' => \App\Models\RiwayatPenghargaan::class,
        'riwayat_organisasi' => \App\Models\RiwayatOrganisasi::class,
        'riwayat_publikasi' => \App\Models\RiwayatPublikasi::class,
    ];

    public function receive(Request $request)
    {
        // Simple Security Auth
        if ($request->header('X-Sync-Secret') !== env('SYNC_SECRET_KEY', 'default_secret_key_123!')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'table_name' => 'required|string',
            'sync_uuid' => 'required|string',
            'action' => 'required|string',
            'payload' => 'required|string'
        ]);

        $modelClass = $this->modelMap[$validated['table_name']] ?? null;
        if (!$modelClass) {
            return response()->json(['error' => 'Table not supported'], 400);
        }

        $payload = json_decode($validated['payload'], true);
        
        // Remove strictly local identifiers to prevent conflict
        unset($payload['id']); 

        try {
            if ($validated['action'] === 'DELETE') {
                $modelClass::where('sync_uuid', $validated['sync_uuid'])->delete();
            } else {
                $existing = $modelClass::where('sync_uuid', $validated['sync_uuid'])->first();

                if (!$existing) {
                    if ($validated['table_name'] === 'users' && !empty($payload['email'])) {
                        $existing = $modelClass::where('email', $payload['email'])->first();
                    } elseif ($validated['table_name'] === 'pegawai' && !empty($payload['nip'])) {
                        $existing = $modelClass::where('nip', $payload['nip'])->first();
                    }
                }

                $payload['sync_uuid'] = $validated['sync_uuid'];
                $payload['last_synced_at'] = now();

                if ($existing) {
                    $existing->fill($payload)->save();
                } else {
                    if ($validated['table_name'] === 'users' && empty($payload['password'])) {
                        $payload['password'] = \Illuminate\Support\Facades\Hash::make('password');
                    }
                    $modelClass::create($payload);
                }
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Sync Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}