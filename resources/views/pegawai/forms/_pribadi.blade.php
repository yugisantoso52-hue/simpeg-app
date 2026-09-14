<x-enterprise.forms.section
    title="Data Pribadi"
    description="Informasi identitas utama, gelar, dan legalitas pegawai">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="NIP / NIK" required>
                <x-enterprise.input name="nip" :value="old('nip', $pegawai->nip ?? '')" required placeholder="Nomor Induk Pegawai / NIK..." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        {{-- Nomor Kartu ASN (Disembunyikan jika PHL) --}}
        <x-enterprise.forms.field>
            <div x-show="kategori !== 'phl'">
                <x-enterprise.form-group label="KARPEG / KARIS / KARSU">
                    <x-enterprise.input name="karpeg_karis_karsu"
                        :value="old('karpeg_karis_karsu', $pegawai->karpeg_karis_karsu ?? '')"
                        placeholder="Nomor Kartu ASN / KARIS / KARSU" />
                </x-enterprise.form-group>
                <div class="mt-2">
                    <x-enterprise.form-group label="Upload Fotocopy KARPEG (PDF/Gambar)">
                        <x-enterprise.file-upload name="file_karpeg" accept=".pdf,.jpg,.jpeg,.png"/>
                        @if(isset($pegawai->file_karpeg) && $pegawai->file_karpeg)
                            <p class="text-xs text-gray-500 mt-1">File saat ini: <a href="{{ route('document.preview', ['path' => $pegawai->file_karpeg]) }}" target="_blank" class="text-blue-600 underline font-semibold">Lihat Berkas KARPEG</a></p>
                        @endif
                    </x-enterprise.form-group>
                </div>
            </div>
            <div x-show="kategori === 'phl'" class="text-xs text-slate-400 pt-7">
                <span class="italic text-amber-600">Khusus Non-ASN / PHL</span>
            </div>
        </x-enterprise.forms.field>

        {{-- NIDN / NUPTK (Khusus Dosen / Tendik Terdaftar) --}}
        <x-enterprise.forms.field>
            <div x-show="kategori === 'dosen' || kategori === 'all'">
                <x-enterprise.form-group label="NIDN / NUPTK (Khusus Dosen)">
                    <x-enterprise.input name="nidn_nuptk"
                        :value="old('nidn_nuptk', $pegawai->nidn_nuptk ?? '')"
                        placeholder="Nomor Induk Dosen Nasional..." />
                </x-enterprise.form-group>
            </div>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nama Lengkap (Tanpa Gelar)" required>
                <x-enterprise.input name="nama" :value="old('nama', $pegawai->nama ?? '')" required placeholder="Nama lengkap..." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Gelar Depan">
                <x-enterprise.input name="gelar_depan" :value="old('gelar_depan', $pegawai->gelar_depan ?? '')" placeholder="Contoh: Dr., Prof., Ns." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Gelar Belakang">
                <x-enterprise.input name="gelar_belakang" :value="old('gelar_belakang', $pegawai->gelar_belakang ?? '')" placeholder="Contoh: M.Kep., Sp.Kep.J., Ph.D." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tempat Lahir">
                <x-enterprise.input name="tempat_lahir" :value="old('tempat_lahir', $pegawai->tempat_lahir ?? '')" placeholder="Kota/Kabupaten lahir..." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tanggal Lahir">
                <x-enterprise.date-picker name="tanggal_lahir" :value="old('tanggal_lahir', isset($pegawai->tanggal_lahir) ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->format('Y-m-d') : '')" />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jenis Kelamin">
                <x-enterprise.select name="jenis_kelamin">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '')=='L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '')=='P')>Perempuan</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="1">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Agama">
                <x-enterprise.select name="agama">
                    <option value="">Pilih Agama</option>
                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $agama)
                        <option value="{{ $agama }}" @selected(old('agama', $pegawai->agama ?? '')==$agama)>
                            {{ $agama }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>