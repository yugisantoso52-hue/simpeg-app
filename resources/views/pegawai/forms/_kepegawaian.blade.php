<x-enterprise.forms.section
    title="Data Kepegawaian"
    description="Unit kerja, jabatan, golongan, jenis pegawai, pendidikan, dan Masa Kerja Golongan">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Unit Kerja">
                <x-enterprise.select name="unit_kerja_id">
                    <option value="">Pilih Unit Kerja</option>
                    @foreach($unitKerja as $item)
                        <option value="{{ $item->id }}" @selected(old('unit_kerja_id', $pegawai->unit_kerja_id ?? '')==$item->id)>
                            {{ $item->nama_unit ?? $item->nama_unit_kerja ?? $item->nama }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jabatan">
                <x-enterprise.select name="jabatan_id">
                    <option value="">Pilih Jabatan</option>
                    @foreach($jabatan as $item)
                        <option value="{{ $item->id }}" @selected(old('jabatan_id', $pegawai->jabatan_id ?? '')==$item->id)>
                            {{ $item->nama_jabatan ?? $item->nama }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Golongan">
                <x-enterprise.select name="golongan_id">
                    <option value="">Pilih Golongan</option>
                    @foreach($golongan as $item)
                        <option value="{{ $item->id }}" @selected(old('golongan_id', $pegawai->golongan_id ?? '')==$item->id)>
                            {{ $item->nama_golongan ?? $item->nama }}
                        </option>
                    @endforeach
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

    {{-- Jenis Jabatan & Angka Kredit --}}
    <x-enterprise.forms.row cols="2">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Jenis Jabatan">
                <x-enterprise.select name="jenis_jabatan">
                    <option value="">Pilih Jenis Jabatan</option>
                    <option value="Fungsional" @selected(old('jenis_jabatan', $pegawai->jenis_jabatan ?? '')=='Fungsional')>Fungsional (Dosen/Pranata Laboratorium/dll)</option>
                    <option value="Struktural" @selected(old('jenis_jabatan', $pegawai->jenis_jabatan ?? '')=='Struktural')>Struktural (Dekan/Kajur/Kaprodi/KTU)</option>
                    <option value="Pelaksana"  @selected(old('jenis_jabatan', $pegawai->jenis_jabatan ?? '')=='Pelaksana')>Pelaksana / Staf Administrasi</option>
                    <option value="Lainnya"    @selected(old('jenis_jabatan', $pegawai->jenis_jabatan ?? '')=='Lainnya')>Lainnya</option>
                </x-enterprise.select>
            </x-enterprise.form-group>
        </x-enterprise.forms.field>

        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Angka Kredit Kumulatif (PAK)">
                <x-enterprise.input
                    type="number"
                    step="0.01"
                    name="angka_kredit"
                    :value="old('angka_kredit', $pegawai->angka_kredit ?? 0)"
                    placeholder="Contoh: 150.00"
                />
            </x-enterprise.form-group>
        </x-enterprise.forms.field>
    </x-enterprise.forms.row>

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

    {{-- Masa Kerja Golongan (MKG) --}}
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

</x-enterprise.forms.section>