<x-enterprise.forms.section
    title="Data Kepegawaian & Jabatan"
    description="Unit kerja, jabatan, golongan, jenis pegawai, dan Angka Kredit PAK">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Unit Kerja / Program Studi" required>
                <x-enterprise.select name="unit_kerja_id" id="field_unit_kerja_id" onchange="syncActiveRiwayatJabatan()" required>
                    <option value="">-- Pilih Unit Kerja --</option>
                    @foreach($unitKerja as $item)
                        <option value="{{ $item->id }}" @selected(old('unit_kerja_id', $pegawai->unit_kerja_id ?? '')==$item->id)>
                            @if($item->parent_id) └─ @endif {{ $item->nama_unit ?? $item->nama_unit_kerja ?? $item->nama }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jabatan Struktural / Fungsional / Pelaksana"
                :hint="auth()->user()?->hasRole('admin') ? null : 'Jabatan ditetapkan melalui pengajuan + verifikasi SK resmi.'">
                <x-enterprise.select name="jabatan_id" id="field_jabatan_id" onchange="autoSyncUnitKerja(this)" :disabled="!auth()->user()?->hasRole('admin')">
                    <option value="">-- Pilih Jabatan --</option>
                    @php
                        $groupedJabatan = $jabatan->groupBy(fn($j) => $j->kelompok_jabatan ?: 'Jabatan Lainnya');
                    @endphp
                    @foreach($groupedJabatan as $kelompok => $items)
                        <optgroup label="📂 {{ strtoupper($kelompok) }}">
                            @foreach($items as $item)
                                <option value="{{ $item->id }}"
                                        data-unit-id="{{ $item->unit_kerja_id }}"
                                        @selected(old('jabatan_id', $pegawai->jabatan_id ?? '')==$item->id)>
                                    {{ $item->nama_jabatan ?? $item->nama }} @if($item->kelas_jabatan)(Grade {{ $item->kelas_jabatan }})@endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        {{-- Golongan (Disembunyikan jika PHL) --}}
        <x-enterprise.forms.field>
            <div x-show="kategori !== 'phl'">
                <x-enterprise.form-group label="Golongan / Ruang">
                    <x-enterprise.select name="golongan_id">
                        <option value="">Pilih Golongan</option>
                        @foreach($golongan as $item)
                            <option value="{{ $item->id }}" @selected(old('golongan_id', $pegawai->golongan_id ?? '')==$item->id)>
                                {{ $item->nama_golongan ?? $item->nama }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </div>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Hirarki Persetujuan & Verifikator Resmi --}}
    @php
        $listAtasan = $atasanList ?? \App\Models\Pegawai::where('status_pegawai', 'Aktif')->when(isset($pegawai->id), fn($q) => $q->where('id', '!=', $pegawai->id))->orderBy('nama')->get();
        $selectedAtasan = old('atasan_id', $pegawai->atasan_id ?? '');
        $selectedPenilai = old('pejabat_penilai_id', $pegawai->pejabat_penilai_id ?? '');
        $selectedAtasanCuti = old('atasan_cuti_id', $pegawai->atasan_cuti_id ?? '');
    @endphp

    <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 my-4 space-y-4">
        <div class="font-bold text-slate-800 text-sm flex items-center justify-between">
            <span class="flex items-center gap-2">🏛️ Hirarki Alur Persetujuan & Verifikator (Approval Flow)</span>
            <span class="text-xs text-slate-500 font-normal">*Digunakan untuk modul Logbook Kinerja & E-Cuti</span>
        </div>

        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Atasan Langsung Utama" hint="Atasan umum struktural pegawai">
                    <x-enterprise.select name="atasan_id">
                        <option value="">-- Otomatis Sesuai Matriks Jabatan --</option>
                        @foreach($listAtasan as $item)
                            <option value="{{ $item->id }}" @selected($selectedAtasan == $item->id)>
                                {{ $item->nama_lengkap ?? $item->nama }} {{ $item->jabatan ? '— ' . ($item->jabatan->nama_jabatan ?? $item->jabatan->nama) : '' }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Pejabat Penilai Kinerja (Logbook)" hint="Pejabat verifikator logbook harian & bulanan">
                    <x-enterprise.select name="pejabat_penilai_id">
                        <option value="">-- Ikut Atasan Langsung / Matriks --</option>
                        @foreach($listAtasan as $item)
                            <option value="{{ $item->id }}" @selected($selectedPenilai == $item->id)>
                                {{ $item->nama_lengkap ?? $item->nama }} {{ $item->jabatan ? '— ' . ($item->jabatan->nama_jabatan ?? $item->jabatan->nama) : '' }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Atasan Pertimbangan Cuti (E-Cuti)" hint="Pejabat pemberi pertimbangan usulan cuti">
                    <x-enterprise.select name="atasan_cuti_id">
                        <option value="">-- Ikut Aturan Cuti Resmi --</option>
                        @foreach($listAtasan as $item)
                            <option value="{{ $item->id }}" @selected($selectedAtasanCuti == $item->id)>
                                {{ $item->nama_lengkap ?? $item->nama }} {{ $item->jabatan ? '— ' . ($item->jabatan->nama_jabatan ?? $item->jabatan->nama) : '' }}
                            </option>
                        @endforeach
                    </x-enterprise.select>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        <div class="text-xs text-slate-500 bg-white p-2.5 rounded-lg border border-slate-200 leading-relaxed">
            <span class="font-semibold text-slate-700 block mb-1">📌 Panduan Hirarki Penilai / Verifikator FKp UNRI:</span>
            <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                <li><strong>Dosen:</strong> Pejabat Penilai Logbook adalah <em>Kaprodi / Ketua Jurusan</em>, Pertimbangan Cuti oleh <em>Ketua Jurusan</em> ➔ PYBMC <em>Dekan / Wadek II</em>.</li>
                <li><strong>Tendik:</strong> Pejabat Penilai Logbook adalah <em>Ka Pokja</em> ➔ <em>Kabag Umum</em>, Pertimbangan Cuti oleh <em>Kabag Umum</em> ➔ PYBMC <em>Wadek II / Dekan</em>.</li>
                <li><strong>Pimpinan Struktural:</strong> Disetujui langsung oleh atasan struktural di atasnya sesuai SK Ketetapan.</li>
            </ul>
        </div>
    </div>

    {{-- Legalitas Profesi Kesehatan (STR & SIP Dosen / Nakes) --}}
    <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-200 my-4 space-y-4">
        <div class="font-bold text-emerald-900 text-sm flex items-center justify-between">
            <span class="flex items-center gap-2">🩺 Legalitas Profesi Kesehatan (STR & SIP Dosen / Tenaga Medis)</span>
            <span class="text-xs text-emerald-700 font-normal">*Khusus Dosen Keperawatan & Tenaga Kesehatan/Ners</span>
        </div>

        {{-- Section STR --}}
        <div class="bg-white p-3.5 rounded-lg border border-emerald-100 space-y-3">
            <span class="font-semibold text-xs text-emerald-800 uppercase tracking-wide flex items-center gap-1.5">
                📜 Surat Tanda Registrasi (STR)
            </span>
            <x-enterprise.forms.row cols="3">
                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Nomor STR">
                        <x-enterprise.input name="nomor_str" :value="old('nomor_str', $pegawai->nomor_str ?? '')" placeholder="Nomor STR perawat/dosen..." />
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>

                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Masa Berlaku STR (Tanggal Kadaluarsa)">
                        <x-enterprise.date-picker name="tanggal_kadaluarsa_str" :value="old('tanggal_kadaluarsa_str', (isset($pegawai->tanggal_kadaluarsa_str) && $pegawai->tanggal_kadaluarsa_str) ? \Carbon\Carbon::parse($pegawai->tanggal_kadaluarsa_str)->format('Y-m-d') : '')" />
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>

                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Upload Dokumen STR (PDF/Gambar)">
                        <x-enterprise.file-upload name="file_str" accept=".pdf,.jpg,.jpeg,.png"/>
                        @if(isset($pegawai->file_str) && $pegawai->file_str && $pegawai->file_str !== '-')
                            <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_str]) }}" target="_blank" class="text-blue-600 underline font-semibold">Lihat Berkas STR</a></p>
                        @endif
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>
            </x-enterprise.forms.row>
        </div>

        {{-- Section SIP --}}
        <div class="bg-white p-3.5 rounded-lg border border-emerald-100 space-y-3">
            <span class="font-semibold text-xs text-emerald-800 uppercase tracking-wide flex items-center gap-1.5">
                🏥 Surat Izin Praktik (SIP)
            </span>
            <x-enterprise.forms.row cols="3">
                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Nomor SIP">
                        <x-enterprise.input name="nomor_sip" :value="old('nomor_sip', $pegawai->nomor_sip ?? '')" placeholder="Nomor SIP..." />
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>

                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Masa Berlaku SIP (Tanggal Kadaluarsa)">
                        <x-enterprise.date-picker name="tanggal_kadaluarsa_sip" :value="old('tanggal_kadaluarsa_sip', (isset($pegawai->tanggal_kadaluarsa_sip) && $pegawai->tanggal_kadaluarsa_sip) ? \Carbon\Carbon::parse($pegawai->tanggal_kadaluarsa_sip)->format('Y-m-d') : '')" />
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>

                <x-enterprise.forms.field>
                    <x-enterprise.form-group label="Upload Dokumen SIP (PDF/Gambar)">
                        <x-enterprise.file-upload name="file_sip" accept=".pdf,.jpg,.jpeg,.png"/>
                        @if(isset($pegawai->file_sip) && $pegawai->file_sip && $pegawai->file_sip !== '-')
                            <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_sip]) }}" target="_blank" class="text-blue-600 underline font-semibold">Lihat Berkas SIP</a></p>
                        @endif
                    </x-enterprise.form-group>
                </x-enterprise.forms.field>
            </x-enterprise.forms.row>
        </div>
    </div>

    {{-- Jenis Jabatan --}}
    <x-enterprise.forms.row cols="1">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jenis Jabatan">
                <x-enterprise.select name="jenis_jabatan">
                    <option value="">Pilih Jenis Jabatan</option>
                    @php
                        $listJenisJabatan = $jenisJabatan ?? \App\Models\JenisJabatan::orderBy('nama_jenis_jabatan')->get();
                        $currentVal = old('jenis_jabatan', $pegawai->jenis_jabatan ?? '');
                    @endphp
                    @foreach($listJenisJabatan as $item)
                        <option value="{{ $item->nama_jenis_jabatan }}" @selected($currentVal == $item->nama_jenis_jabatan)>
                            {{ $item->nama_jenis_jabatan }}
                        </option>
                    @endforeach
                    @if($currentVal && !$listJenisJabatan->contains('nama_jenis_jabatan', $currentVal))
                        <option value="{{ $currentVal }}" selected>{{ $currentVal }}</option>
                    @endif
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Penilaian Angka Kredit (PAK) - Khusus Dosen & Pranata Laboratorium Pendidikan (PLP) --}}
    <div x-show="kategori !== 'phl'" class="p-4 bg-blue-50/50 rounded-xl border border-blue-200 my-4 space-y-4">
        <div class="font-bold text-blue-900 text-sm flex items-center justify-between">
            <span class="flex items-center gap-2">📊 Penilaian Angka Kredit (PAK) — Khusus Dosen & Pranata Laboratorium Pendidikan (PLP)</span>
            <span class="text-xs text-blue-600 font-normal">*Syarat Wajib Usulan Kenaikan Pangkat Fungsional</span>
        </div>
        <x-enterprise.forms.row cols="4">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Angka Kredit Kumulatif">
                    <x-enterprise.input
                        type="number"
                        step="0.01"
                        name="angka_kredit"
                        :value="old('angka_kredit', $pegawai->angka_kredit ?? 0)"
                        placeholder="Contoh: 150.00"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Nomor SK PAK">
                    <x-enterprise.input
                        name="nomor_pak"
                        :value="old('nomor_pak', $pegawai->nomor_pak ?? '')"
                        placeholder="Nomor SK PAK..."
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal SK PAK">
                    <x-enterprise.date-picker
                        name="tanggal_pak"
                        :value="old('tanggal_pak', (isset($pegawai->tanggal_pak) && $pegawai->tanggal_pak) ? \Carbon\Carbon::parse($pegawai->tanggal_pak)->format('Y-m-d') : '')"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload Dokumen/SK PAK (PDF/Gambar)">
                    <x-enterprise.file-upload name="file_pak" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($pegawai->file_pak) && $pegawai->file_pak && $pegawai->file_pak !== '-')
                        <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_pak]) }}" target="_blank" class="text-blue-600 underline font-semibold">Lihat Berkas PAK</a></p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jenis Pegawai">
                <x-enterprise.select name="jenis_pegawai">
                    <option value="">Pilih Jenis Pegawai</option>
                    <option value="Dosen"  @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? '')=='Dosen')>Dosen</option>
                    <option value="PNS"    @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? '')=='PNS')>PNS</option>
                    <option value="PPPK"   @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? '')=='PPPK')>PPPK</option>
                    <option value="PHL"    @selected(old('jenis_pegawai', $pegawai->jenis_pegawai ?? '')=='PHL' || old('jenis_pegawai', $pegawai->jenis_pegawai ?? '')=='Honorer')>PHL</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Status ASN">
                <x-enterprise.select name="status_asn">
                    <option value="">Pilih Status ASN</option>
                    <option value="ASN"     @selected(old('status_asn', $pegawai->status_asn ?? '')=='ASN')>ASN</option>
                    <option value="Non ASN" @selected(old('status_asn', $pegawai->status_asn ?? '')=='Non ASN')>Non ASN</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Pendidikan Terakhir">
                <x-enterprise.select name="pendidikan_terakhir">
                    <option value="">Pilih Jenjang Pendidikan</option>
                    @foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'] as $jenjang)
                        <option value="{{ $jenjang }}" @selected(old('pendidikan_terakhir', $pegawai->pendidikan_terakhir ?? '')==$jenjang)>
                            {{ $jenjang }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Masa Kerja Golongan (MKG) - Khusus ASN/PNS/Tendik/Dosen --}}
    <div x-show="kategori !== 'phl'">
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="MKG — Masa Kerja Golongan (Tahun)">
                    <x-enterprise.input
                        type="number"
                        name="mkg_tahun"
                        :value="old('mkg_tahun', $pegawai->mkg_tahun ?? 0)"
                        min="0"
                        max="40"
                        placeholder="0"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="MKG — Masa Kerja Golongan (Bulan)">
                    <x-enterprise.input
                        type="number"
                        name="mkg_bulan"
                        :value="old('mkg_bulan', $pegawai->mkg_bulan ?? 0)"
                        min="0"
                        max="11"
                        placeholder="0"
                    />
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

</x-enterprise.forms.section>

<script>
    function autoSyncUnitKerja(selectElem) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        if (!selectedOpt) return;
        const unitId = selectedOpt.getAttribute('data-unit-id');
        const unitSelect = document.getElementById('field_unit_kerja_id');
        if (unitId && unitSelect) {
            unitSelect.value = unitId;
        }
        syncActiveRiwayatJabatan();
    }

    function syncActiveRiwayatJabatan() {
        const unitSelect = document.getElementById('field_unit_kerja_id');
        const jabatanSelect = document.getElementById('field_jabatan_id');
        const unitId = unitSelect ? unitSelect.value : null;
        const jabId = jabatanSelect ? jabatanSelect.value : null;

        // Cari baris riwayat jabatan yang berstatus 'aktif' di tabel Riwayat Jabatan
        const rows = document.querySelectorAll('#jabatan-tbody tr.jabatan-row');
        rows.forEach(row => {
            const statusSelect = row.querySelector('select[name$="[status]"]');
            if (statusSelect && statusSelect.value.toLowerCase() === 'aktif') {
                if (unitId) {
                    const rUnit = row.querySelector('select[name$="[unit_kerja_id]"]');
                    if (rUnit) rUnit.value = unitId;
                }
                if (jabId) {
                    const rJab = row.querySelector('select[name$="[jabatan_id]"]');
                    if (rJab) rJab.value = jabId;
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('field_unit_kerja_id');
        if (unitSelect) {
            unitSelect.addEventListener('change', syncActiveRiwayatJabatan);
        }
        const jabSelect = document.getElementById('field_jabatan_id');
        if (jabSelect) {
            jabSelect.addEventListener('change', syncActiveRiwayatJabatan);
        }
    });
</script>