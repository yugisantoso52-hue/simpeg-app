<?php

namespace App\Traits;

trait AuthorizesRiwayatOwner
{
    /**
     * Pastikan pengguna adalah admin atau pemilik sah data riwayat kepegawaian
     */
    protected function authorizeOwnerOrAdmin($existing): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if ($user->hasRole('admin')) {
            return;
        }

        $recordPegawaiId = null;
        if (is_object($existing)) {
            $recordPegawaiId = $existing->pegawai_id ?? null;
        } elseif (is_array($existing)) {
            $recordPegawaiId = $existing['pegawai_id'] ?? null;
        }

        if (!$recordPegawaiId || (int)$user->pegawai_id !== (int)$recordPegawaiId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah atau menghapus data riwayat milik pegawai lain.');
        }
    }
}
