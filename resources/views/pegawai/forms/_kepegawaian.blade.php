<x-enterprise.forms.section
    title="Data Kepegawaian & Jabatan"
    description="Unit kerja, jabatan, golongan, jenis pegawai, dan Angka Kredit PAK">

    <x-enterprise.forms.row cols="3">
        <x-enterprise.forms.field>
            <x-enterprise.form-group label="Unit Kerja / Program Studi" required>
                <x-enterprise.select name="unit_kerja_id" required>
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
                    @if(isset($pegawai->file_pak) && $pegawai->file_pak)
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