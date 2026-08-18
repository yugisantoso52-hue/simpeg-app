<x-enterprise.forms.section
    title="Informasi Kontak"
    description="Informasi komunikasi dan domisili pegawai">

    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Email">
                <x-enterprise.input type="email" name="email" :value="old('email', $pegawai->email ?? '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Nomor HP">
                <x-enterprise.input name="no_hp" :value="old('no_hp', $pegawai->no_hp ?? '')"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field colspan="2">
            <x-enterprise.form-group label="Alamat">
                <x-enterprise.textarea name="alamat" rows="4">{{ old('alamat', $pegawai->alamat ?? '') }}</x-enterprise.textarea>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>