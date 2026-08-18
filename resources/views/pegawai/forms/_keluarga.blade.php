<x-enterprise.forms.section
    title="Data Keluarga"
    description="Informasi hubungan keluarga dan tanggungan">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Status Pernikahan">
                <x-enterprise.select name="status_pernikahan">
                    <option value="">Pilih Status</option>
                    <option value="Belum Menikah" @selected(old('status_pernikahan', $pegawai->status_pernikahan ?? '')=='Belum Menikah')>Belum Menikah</option>
                    <option value="Menikah" @selected(old('status_pernikahan', $pegawai->status_pernikahan ?? '')=='Menikah')>Menikah</option>
                    <option value="Cerai" @selected(old('status_pernikahan', $pegawai->status_pernikahan ?? '')=='Cerai')>Cerai</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nama Pasangan">
                <x-enterprise.input name="nama_pasangan" :value="old('nama_pasangan', $pegawai->nama_pasangan ?? '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jumlah Anak">
                <x-enterprise.input type="number" name="jumlah_anak" :value="old('jumlah_anak', $pegawai->jumlah_anak ?? 0)"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>