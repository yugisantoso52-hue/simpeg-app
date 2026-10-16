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

        if ($user->hasRole(['admin', 'super-admin', 'Super Admin', 'Administrator']) || 
            (method_exists($user, 'canAccessExecutiveKepegawaianMenus') && $user->canAccessExecutiveKepegawaianMenus())) {
            return;
        }

        $recordPegawaiId = null;
        if (is_object($existing)) {
            $recordPegawaiId = $existing->pegawai_id ?? null;
        } elseif (is_array($existing)) {
            $recordPegawaiId = $existing['pegawai_id'] ?? null;
        }

        $userPegawaiId = $user->pegawai_id ?? $user->pegawai?->id;

        if (!$recordPegawaiId || (int)$userPegawaiId !== (int)$recordPegawaiId) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat, mengubah, atau menghapus data riwayat milik pegawai lain.');
        }
    }
}
