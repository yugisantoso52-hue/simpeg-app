<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetPasswordChangeFlagCommand extends Command
{
    protected $signature = 'user:reset-password-flag {--all : Reset seluruh akun user} {--email= : Reset untuk email tertentu}';
    protected $description = 'Mereset status must_change_password menjadi false pada akun pengguna agar langsung dapat mengakses dashboard';

    public function handle(): int
    {
        $all = $this->option('all');
        $email = $this->option('email');

        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("Pengguna dengan email '{$email}' tidak ditemukan.");
                return Command::FAILURE;
            }

            $user->update(['must_change_password' => false]);
            $this->info("✅ Flag must_change_password untuk [{$email}] berhasil diubah menjadi FALSE.");
            return Command::SUCCESS;
        }

        if ($all) {
            $count = User::query()->update(['must_change_password' => false]);
            $this->info("✅ Sebanyak {$count} akun pengguna berhasil direset (must_change_password = FALSE).");
            return Command::SUCCESS;
        }

        // Default: Reset admin & testing accounts
        $adminCount = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
            ->orWhere('email', 'admin@simpeg.test')
            ->update(['must_change_password' => false]);

        $this->info("✅ Akun Administrator ({$adminCount} akun) telah direset agar dapat langsung mengakses Dashboard tanpa popup ganti password.");
        $this->comment("Tips: Gunakan '--all' untuk mereset seluruh akun pegawai sekaligus: php artisan user:reset-password-flag --all");

        return Command::SUCCESS;
    }
}