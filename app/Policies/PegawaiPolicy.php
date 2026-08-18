<?php

namespace App\Policies;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PegawaiPolicy
{
    /**
     * Cek apakah user bisa melihat daftar semua pegawai
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'pimpinan']);
    }

    /**
     * Cek akses untuk melihat detail profil pegawai tertentu
     */
    public function view(User $user, Pegawai $pegawai): bool
    {
        // Admin dan Pimpinan bisa melihat semua data pegawai
        if ($user->hasRole(['admin', 'pimpinan'])) {
            return true;
        }

        // Pegawai biasa HANYA bisa melihat datanya sendiri
        return $user->pegawai_id === $pegawai->id;
    }

    /**
     * Cek akses untuk membuat data pegawai baru
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Cek akses untuk mengubah data pegawai
     */
    public function update(User $user, Pegawai $pegawai): bool
    {
        // 1. Admin boleh mengedit data pegawai siapapun
        if ($user->hasRole('admin')) {
            return true;
        }

        // 2. Pegawai biasa DIIZINKAN mengedit data pribadinya sendiri
        return $user->hasRole('pegawai') && $user->pegawai_id === $pegawai->id;
    }

    /**
     * Cek akses untuk menghapus pegawai
     */
    public function delete(User $user, Pegawai $pegawai): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Restore data pegawai
     */
    public function restore(User $user, Pegawai $pegawai): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Hapus permanen data pegawai
     */
    public function forceDelete(User $user, Pegawai $pegawai): bool
    {
        return $user->hasRole('admin');
    }
}