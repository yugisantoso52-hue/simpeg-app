<div class="mt-6 border-t pt-6 space-y-3">
    <div>
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Jabatan</h3>
        <p class="text-xs text-gray-500">
            Riwayat jabatan hanya dapat berubah melalui verifikasi SK oleh Admin atau Ka Pokja Keuangan dan Kepegawaian.
            Gunakan bagian <strong>Ajukan Jabatan Struktural</strong> di bawah formulir ini untuk mengusulkan jabatan baru.
        </p>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-700 font-medium">
                <tr>
                    <th class="px-3 py-2 text-left">Jabatan</th>
                    <th class="px-3 py-2 text-left">Unit Kerja</th>
                    <th class="px-3 py-2 text-center">TMT</th>
                    <th class="px-3 py-2 text-left">Nomor SK</th>
                    <th class="px-3 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($pegawai->riwayatJabatan as $rj)
                    <tr>
                        <td class="px-3 py-2">{{ $rj->jabatan->nama_jabatan ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $rj->unitKerja->nama_unit ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ $rj->tmt_jabatan?->format('d-m-Y') ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $rj->nomor_sk ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ ucfirst(strtolower($rj->status ?? '-')) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-3 text-center text-xs text-gray-500">Belum ada riwayat jabatan terverifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
