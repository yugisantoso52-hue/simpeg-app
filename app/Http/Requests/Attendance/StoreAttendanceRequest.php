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
        ];
    }
}
