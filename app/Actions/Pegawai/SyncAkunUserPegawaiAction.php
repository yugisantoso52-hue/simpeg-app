<?php

namespace App\Actions\Pegawai;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SyncAkunUserPegawaiAction
{
    /**
     * Sinkronisasikan akun login user ke data pegawai
     */
    public function execute(Pegawai $pegawai, ?string $oldNip = null, bool $isCreate = false): void
    {
        if ($isCreate) {
            $this->createUserAccount($pegawai);
        } else {
            $this->updateUserAccount($pegawai, $oldNip);
        }
    }

    /**
     * Buat akun login baru untuk pegawai baru
     */
    protected function createUserAccount(Pegawai $pegawai): void
    {
        $rolePegawai = Role::where('name', 'pegawai')->first();
        if (!$rolePegawai) {
            return;
        }

        $nipClean = preg_replace('/[^0-9]/', '', (string)($pegawai->nip ?? ''));
        $identifier = !empty($nipClean) ? $nipClean : 'pegawai_' . $pegawai->id;
        $emailLogin = $identifier . '@staff.unri.ac.id';
        $passwordDefault = 'Password';

        User::create([
            'name'                 => $pegawai->nama,
            'email'                => $emailLogin,
            'password'             => Hash::make($passwordDefault),
            'role_id'              => $rolePegawai->id,
            'pegawai_id'           => $pegawai->id,
            'must_change_password' => true,
        ]);
    }

    /**
     * Perbarui akun login user jika NIP, email, atau nama berubah
     */
    protected function updateUserAccount(Pegawai $pegawai, ?string $oldNip = null): void
    {
        if ($oldNip !== null && $oldNip !== $pegawai->nip) {
            $user = User::where('pegawai_id', $pegawai->id)->first();
            if ($user) {
                $user->email = $pegawai->nip . '@staff.unri.ac.id';
                $user->save();
            }
        }

        // Sinkronkan nama dan email tabel users
        if ($pegawaiUser = User::where('pegawai_id', $pegawai->id)->first()) {
            $pegawaiUser->update([
                'name'  => $pegawai->nama,
                'email' => $pegawai->email ?: $pegawaiUser->email,
            ]);
        }
    }
}
