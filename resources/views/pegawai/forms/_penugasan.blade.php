@php
    $kelompokList = ($kelompokPenugasan ?? \App\Models\KelompokPenugasan::orderBy('kategori')->orderBy('urutan')->get())->groupBy('kategori');
    $penugasanSaatIni = isset($pegawai) ? $pegawai->penugasan->keyBy('kelompok_penugasan_id') : collect();
    $oldPenugasan = old('penugasan');
@endphp

<div class="mt-6 border-t pt-6 space-y-4">
    <div>
        <h3 class="text-lg font-semibold text-gray-800">Penugasan &amp; Keanggotaan Unit</h3>
        <p class="text-xs text-gray-500">
            Pilih kelompok/unit tempat Anda bertugas (boleh lebih dari satu). Data ini langsung tampil pada Peta Jabatan
            sehingga kelebihan atau kekurangan formasi dapat dipantau. Tidak memerlukan verifikasi dan tidak mengubah hak akses.
        </p>
    </div>

    <input type="hidden" name="penugasan_submitted" value="1">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach(\App\Models\KelompokPenugasan::KATEGORI as $kat => $judul)
            <div class="rounded-xl border border-gray-200 bg-white p-3">
                <div class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">{{ $judul }}</div>
                <div class="space-y-1.5">
                    @foreach(($kelompokList[$kat] ?? collect()) as $kel)
                        @php
                            $defaultPeran = match($kel->nama) {
                                'Ketua Senat' => 'Ketua',
                                'Sekretaris Senat' => 'Sekretaris',
                                'GPM S1', 'GPM S2', 'GPM NERS' => 'Ketua',
                                default => 'Anggota',
                            };
                            $aktif = is_array($oldPenugasan)
                                ? isset($oldPenugasan[$kel->id])
                                : $penugasanSaatIni->has($kel->id);
                            $peranVal = is_array($oldPenugasan)
                                ? ($oldPenugasan[$kel->id]['peran'] ?? $defaultPeran)
                                : ($penugasanSaatIni[$kel->id]->peran ?? $defaultPeran);
                        @endphp
                        <div x-data="{ on: {{ $aktif ? 'true' : 'false' }} }"
                             class="flex items-center justify-between gap-2 rounded-lg border px-2 py-1.5 text-xs"
                             :class="on ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-gray-50'">
                            <label class="flex items-center gap-2 cursor-pointer min-w-0">
                                <input type="checkbox" x-model="on" class="rounded border-gray-300 text-emerald-600">
                                <span class="font-semibold text-gray-800 truncate">{{ $kel->nama }}</span>
                                @if($kel->kebutuhan)
                                    <span class="text-[10px] text-gray-400 font-mono">({{ $kel->kebutuhan }} org)</span>
                                @endif
                            </label>
                            <input type="hidden" name="penugasan[{{ $kel->id }}][kelompok_penugasan_id]" value="{{ $kel->id }}" :disabled="!on">
                            <select name="penugasan[{{ $kel->id }}][peran]" :disabled="!on"
                                    class="border rounded px-1.5 py-0.5 text-[11px] w-28 shrink-0">
                                @foreach(\App\Models\PegawaiPenugasan::PERAN as $peran)
                                    <option value="{{ $peran }}" @selected($peranVal === $peran)>{{ $peran }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
