<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'attendance_type' => ['required', 'string', 'in:wfo,wfh'],
            'action' => ['nullable', 'string', 'in:check_in,check_out'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            
            // Pilar 1: Sensor GPS & Mock Location
            'accuracy' => ['required', 'numeric'],
            'altitude' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric'],
            'is_mock' => ['nullable', 'boolean'],
            'client_timestamp' => ['nullable', 'numeric'],

            // Pilar 2: Liveness & Face Verification
            'liveness_verified' => ['nullable', 'boolean'],
            'liveness_challenge' => ['nullable', 'string', 'max:50'],
            'face_similarity_score' => ['nullable', 'numeric', 'between:0,100'],

            // Pilar 3: Integritas Perangkat & Anti-Replay
            'device_fingerprint' => ['required', 'string', 'max:64'],
            'device_platform' => ['nullable', 'string', 'max:100'],
            'token' => ['nullable', 'required_without:is_offline_sync', 'string', 'max:100'],

            // Pilar 4: PWA Offline-First Sync & Cryptographic Proof
            'is_offline_sync' => ['nullable', 'boolean'],
            'offline_captured_at' => ['nullable', 'required_if:is_offline_sync,true,1', 'numeric'],
            'offline_proof_hash' => ['nullable', 'required_if:is_offline_sync,true,1', 'string', 'size:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_type.required' => 'Pilih jenis presensi (WFO atau WFH).',
            'attendance_type.in' => 'Jenis presensi hanya boleh WFO atau WFH.',
            'latitude.required' => 'Koordinat Latitude tidak terdeteksi. Harap izinkan akses lokasi (GPS) pada peramban Anda.',
            'latitude.numeric' => 'Format koordinat Latitude tidak valid.',
            'longitude.required' => 'Koordinat Longitude tidak terdeteksi. Harap izinkan akses lokasi (GPS) pada peramban Anda.',
            'longitude.numeric' => 'Format koordinat Longitude tidak valid.',
            'photo.required' => 'Foto selfie wajib diambil langsung melalui kamera.',
            'accuracy.required' => 'Data akurasi sinyal GPS tidak terbaca. Pastikan GPS aktif.',
            'device_fingerprint.required' => 'Sidik jari perangkat gagal diidentifikasi oleh peramban.',
            'token.required' => 'Token sesi presensi tidak ditemukan. Harap muat ulang halaman.',
        ];
    }
}
