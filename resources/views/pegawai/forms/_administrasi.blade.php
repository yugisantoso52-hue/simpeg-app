@php
    $activeJabatan = isset($pegawai) ? $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first() : null;
    $activePangkat = isset($pegawai) ? $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->first() : null;
@endphp
<x-enterprise.forms.section
    title="Administrasi Kepegawaian"
    description="Data tanggal masuk, TMT, upload dokumen SK, keaktifan, dan dokumen foto">

    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tanggal Masuk">
                <x-enterprise.date-picker name="tanggal_masuk" :value="old('tanggal_masuk', isset($pegawai->tanggal_masuk) ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('Y-m-d') : '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Status Pegawai">
                <x-enterprise.select name="status_pegawai">
                    <option value="Aktif" @selected(old('status_pegawai', $pegawai->status_pegawai ?? 'Aktif') == 'Aktif')>Aktif</option>
                    <option value="Non Aktif" @selected(old('status_pegawai', $pegawai->status_pegawai ?? '') == 'Non Aktif')>Non Aktif</option>
                    <option value="Pensiun" @selected(old('status_pegawai', $pegawai->status_pegawai ?? '') == 'Pensiun')>Pensiun</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

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

    {{-- Detail SK Pangkat Terakhir --}}
    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nomor SK Pangkat Terakhir">
                <x-enterprise.input name="nomor_sk_pangkat_terakhir" :value="old('nomor_sk_pangkat_terakhir', $activePangkat->nomor_sk ?? '')" placeholder="Masukkan Nomor SK Pangkat Terakhir..."/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tanggal SK Pangkat Terakhir">
                <x-enterprise.date-picker name="tanggal_sk_pangkat_terakhir" :value="old('tanggal_sk_pangkat_terakhir', (isset($activePangkat->tanggal_sk) && $activePangkat->tanggal_sk) ? \Carbon\Carbon::parse($activePangkat->tanggal_sk)->format('Y-m-d') : '')"/>
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