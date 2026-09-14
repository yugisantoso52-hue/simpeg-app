public function rules(): array
{
    return [
        'pegawai_id'  => ['required', 'exists:pegawai,id'],
        'jabatan_id'  => ['required', 'exists:jabatan,id'],
        'nomor_sk'    => ['nullable', 'string', 'max:255'],
        'tanggal_sk'  => ['nullable', 'date'], // ← Tambahkan ini
        'tmt_jabatan' => ['nullable', 'date'],
        'keterangan'  => ['nullable', 'string'],
    ];
}