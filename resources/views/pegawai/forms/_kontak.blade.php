<x-enterprise.forms.section
    title="Informasi Kontak & Domisili"
    description="Informasi komunikasi, kontak darurat, alamat asal dan domisili pegawai">

    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Email">
                <x-enterprise.input type="email" name="email" :value="old('email', $pegawai->email ?? '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nomor HP / WhatsApp">
                <x-enterprise.input name="no_hp" :value="old('no_hp', $pegawai->no_hp ?? '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Kontak Darurat --}}
    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nama Kontak Darurat">
                <x-enterprise.input name="nama_kontak_darurat" :value="old('nama_kontak_darurat', $pegawai->nama_kontak_darurat ?? '')" placeholder="Nama keluarga/kerabat"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Hubungan Kontak Darurat">
                <x-enterprise.input name="hubungan_kontak_darurat" :value="old('hubungan_kontak_darurat', $pegawai->hubungan_kontak_darurat ?? '')" placeholder="Contoh: Suami / Istri / Orang Tua / Saudara"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nomor HP Darurat">
                <x-enterprise.input name="no_hp_darurat" :value="old('no_hp_darurat', $pegawai->no_hp_darurat ?? '')" placeholder="Nomor telepon darurat"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Alamat KTP & Domisili --}}
    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Alamat Sesuai KTP / Asal">
                <x-enterprise.textarea name="alamat" rows="3">{{ old('alamat', $pegawai->alamat ?? '') }}</x-enterprise.textarea>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Alamat Domisili Saat Ini">
                <x-enterprise.textarea name="alamat_domisili" rows="3" placeholder="Isi jika berbeda dengan alamat KTP">{{ old('alamat_domisili', $pegawai->alamat_domisili ?? '') }}</x-enterprise.textarea>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Kota / Kabupaten Domisili">
                <x-enterprise.input name="kota_domisili" :value="old('kota_domisili', $pegawai->kota_domisili ?? '')" placeholder="Contoh: Pekanbaru"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Provinsi Domisili">
                <x-enterprise.input name="provinsi" :value="old('provinsi', $pegawai->provinsi ?? '')" placeholder="Contoh: Riau"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Kode Pos">
                <x-enterprise.input name="kode_pos" :value="old('kode_pos', $pegawai->kode_pos ?? '')" placeholder="Contoh: 28293"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>