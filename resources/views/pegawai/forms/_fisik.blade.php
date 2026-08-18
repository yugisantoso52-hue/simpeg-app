<x-enterprise.forms.section
    title="Data Fisik"
    description="Informasi fisik dan medis pegawai">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Golongan Darah">
                <x-enterprise.select name="gol_darah">
                    <option value="">-</option>
                    @foreach(['A','B','AB','O'] as $gol)
                        <option value="{{ $gol }}" @selected(old('gol_darah', $pegawai->gol_darah ?? '')==$gol)>
                            {{ $gol }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Tinggi Badan (cm)">
                <x-enterprise.input type="number" name="tinggi_badan" :value="old('tinggi_badan', $pegawai->tinggi_badan ?? 0)"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Berat Badan (kg)">
                <x-enterprise.input type="number" name="berat_badan" :value="old('berat_badan', $pegawai->berat_badan ?? 0)"/>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

</x-enterprise.forms.section>