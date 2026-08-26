<x-enterprise.forms.section
    title="Data Pribadi"
    description="Informasi identitas utama dan legalitas pegawai">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="NIP" required>
                <x-enterprise.input name="nip" :value="old('nip', $pegawai->nip ?? '')" required />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="KARPEG / KARIS / KARSU">
                <x-enterprise.input name="karpeg_karis_karsu"
                    :value="old('karpeg_karis_karsu', $pegawai->karpeg_karis_karsu ?? '')"
                    placeholder="Nomor Kartu ASN / KARIS / KARSU" />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="NIDN / NUPTK">
                <x-enterprise.input name="nidn_nuptk"
                    :value="old('nidn_nuptk', $pegawai->nidn_nuptk ?? '')"
                    placeholder="NIDN (Dosen) atau NUPTK (Tendik)" />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nama Lengkap" required>
                <x-enterprise.input name="nama" :value="old('nama', $pegawai->nama ?? '')" required />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Gelar Depan">
                <x-enterprise.input name="gelar_depan" :value="old('gelar_depan', $pegawai->gelar_depan ?? '')" placeholder="Contoh: Dr., Prof." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Gelar Belakang">
                <x-enterprise.input name="gelar_belakang" :value="old('gelar_belakang', $pegawai->gelar_belakang ?? '')" placeholder="Contoh: M.Kep., Sp.KJ." />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tempat Lahir">
                <x-enterprise.input name="tempat_lahir" :value="old('tempat_lahir', $pegawai->tempat_lahir ?? '')" />
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