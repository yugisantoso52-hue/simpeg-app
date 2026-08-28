@php
    $activeJabatan = isset($pegawai) ? $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first() : null;
    $activePangkat = isset($pegawai) ? $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->first() : null;
@endphp
<x-enterprise.forms.section
    title="Administrasi Kepegawaian & Legalitas"
    description="Data tanggal masuk, TMT, upload SK, masa kontrak (PHL), dan pensiun">

    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tanggal Masuk / TMT Awal">
                <x-enterprise.date-picker name="tanggal_masuk" :value="old('tanggal_masuk', isset($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('Y-m-d') : '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Status Pegawai">
                <x-enterprise.select name="status_pegawai">
                    <option value="Aktif"         @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'Aktif') == 'Aktif')>Aktif</option>
                    <option value="Tugas Belajar" @selected(old('status_pegawai', $pegawai->status_pegawai ?? '') == 'Tugas Belajar')>Tugas Belajar</option>
                    <option value="Non Aktif"     @selected(old('status_pegawai', $pegawai->status_pegawai ?? '') == 'Non Aktif')>Non Aktif</option>
                    <option value="Pensiun"       @selected(old('status_pegawai', $pegawai->status_pegawai ?? '') == 'Pensiun')>Pensiun</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- KHUSUS PHL / TENAGA KONTRAK --}}
    <div x-show="kategori === 'phl' || kategori === 'all'" class="p-4 bg-amber-50 rounded-xl border border-amber-200 my-4 space-y-4">
        <div class="font-bold text-amber-900 text-sm flex items-center gap-2">
            <span>👷 Detail Kontrak Kerja PHL</span>
        </div>
        <x-enterprise.forms.row cols="3">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Jenis Kontrak">
                    <x-enterprise.input name="jenis_kontrak" :value="old('jenis_kontrak', $pegawai->jenis_kontrak ?? '')" placeholder="Contoh: Kontrak Tahunan BLU"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal Mulai Kontrak">
                    <x-enterprise.date-picker name="tanggal_kontrak_mulai" :value="old('tanggal_kontrak_mulai', (isset($pegawai->tanggal_kontrak_mulai) && $pegawai->tanggal_kontrak_mulai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_mulai)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal Selesai Kontrak">
                    <x-enterprise.date-picker name="tanggal_kontrak_selesai" :value="old('tanggal_kontrak_selesai', (isset($pegawai->tanggal_kontrak_selesai) && $pegawai->tanggal_kontrak_selesai) ? \Carbon\Carbon::parse($pegawai->tanggal_kontrak_selesai)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- KHUSUS DOSEN & TENDIK (ASN/PNS/PPPK): SK Pertama, Pangkat Terakhir & KGB --}}
    <div x-show="kategori !== 'phl'">
        {{-- Baris TMT SK Pertama --}}
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="TMT SK Pertama">
                    <x-enterprise.date-picker name="tmt_sk_pertama" :value="old('tmt_sk_pertama', isset($pegawai->tmt_sk_pertama) ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload SK Pertama (PDF/Gambar)">
                    <x-enterprise.file-upload name="file_sk_pertama" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($pegawai->file_sk_pertama) && $pegawai->file_sk_pertama)
                        <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_pertama]) }}" target="_blank" class="text-blue-600 underline">Lihat Dokumen</a></p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        {{-- Detail SK Pertama --}}
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Nomor SK Pertama">
                    <x-enterprise.input name="nomor_sk_pertama" :value="old('nomor_sk_pertama', $activeJabatan->nomor_sk ?? '')" placeholder="Masukkan Nomor SK Pertama..."/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal SK Pertama">
                    <x-enterprise.date-picker name="tanggal_sk_pertama" :value="old('tanggal_sk_pertama', (isset($activeJabatan->tanggal_sk) && $activeJabatan->tanggal_sk) ? \Carbon\Carbon::parse($activeJabatan->tanggal_sk)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        {{-- Baris TMT Pangkat Terakhir --}}
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="TMT Pangkat Terakhir">
                    <x-enterprise.date-picker name="tmt_pangkat_terakhir" :value="old('tmt_pangkat_terakhir', isset($pegawai->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload SK Pangkat Terakhir (PDF/Gambar)">
                    <x-enterprise.file-upload name="file_sk_pangkat_terakhir" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($pegawai->file_sk_pangkat_terakhir) && $pegawai->file_sk_pangkat_terakhir)
                        <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_pangkat_terakhir]) }}" target="_blank" class="text-blue-600 underline">Lihat Dokumen</a></p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        {{-- Baris TMT KGB Terakhir --}}
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="TMT KGB Terakhir">
                    <x-enterprise.date-picker name="tmt_kgb_terakhir" :value="old('tmt_kgb_terakhir', isset($pegawai->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($pegawai->tmt_kgb_terakhir)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Upload SK KGB Terakhir (PDF/Gambar)">
                    <x-enterprise.file-upload name="file_sk_kgb_terakhir" accept=".pdf,.jpg,.jpeg,.png"/>
                    @if(isset($pegawai->file_sk_kgb_terakhir) && $pegawai->file_sk_kgb_terakhir)
                        <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_sk_kgb_terakhir]) }}" target="_blank" class="text-blue-600 underline">Lihat Dokumen</a></p>
                    @endif
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>

        {{-- Batas Usia Pensiun (BUP) & Pensiun --}}
        <x-enterprise.forms.row cols="2">
            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Batas Usia Pensiun (BUP)">
                    <x-enterprise.select name="batas_usia_pensiun">
                        <option value="">Pilih BUP</option>
                        <option value="56" @selected(old('batas_usia_pensiun', $pegawai->batas_usia_pensiun ?? '') == 56)>56 Tahun</option>
                        <option value="58" @selected(old('batas_usia_pensiun', $pegawai->batas_usia_pensiun ?? '') == 58)>58 Tahun (Tendik Pelaksana/Fungsional Ahli Pertama/Muda)</option>
                        <option value="60" @selected(old('batas_usia_pensiun', $pegawai->batas_usia_pensiun ?? '') == 60)>60 Tahun (Fungsional Ahli Madya / Struktural)</option>
                        <option value="65" @selected(old('batas_usia_pensiun', $pegawai->batas_usia_pensiun ?? '') == 65)>65 Tahun (Dosen / Fungsional Ahli Utama)</option>
                    </x-enterprise.select>
                    <span class="text-[11px] text-gray-500 mt-1 block">Otomatis dihitung dari tanggal lahir + BUP jika dikosongkan.</span>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>

            <x-enterprise.forms.field>
                <x-enterprise.form-group label="Tanggal Pensiun (Manual Override)">
                    <x-enterprise.date-picker name="tanggal_pensiun" :value="old('tanggal_pensiun', (isset($pegawai->tanggal_pensiun) && $pegawai->tanggal_pensiun) ? \Carbon\Carbon::parse($pegawai->tanggal_pensiun)->format('Y-m-d') : '')"/>
                </x-enterprise.form-group>
            </x-enterprise.forms.field>
        </x-enterprise.forms.row>
    </div>

    {{-- Baris Foto Pegawai --}}
    <x-enterprise.forms.row cols="1">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Foto Pegawai">
                @if(isset($pegawai) && $pegawai->foto)
                    <div class="mb-2">
                        <img src="{{ $pegawai->foto_url }}" width="100" class="rounded border shadow-sm h-28 w-24 object-cover">
                        <span class="text-xs text-gray-500 block mt-1">Foto saat ini</span>
                    </div>
                @endif
                <x-enterprise.file-upload name="foto" accept="image/*"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>