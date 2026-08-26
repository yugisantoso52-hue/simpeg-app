<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🎓</span> Tambah Data Tugas Belajar / Izin Belajar
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pencatatan studi lanjut S2, S3, dan Spesialis dosen serta tenaga kependidikan
                </p>
            </div>
            <a href="{{ route('tugas-belajar.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 md:p-8">

                @if($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                        <div class="font-bold mb-1">Terdapat kesalahan input:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('tugas-belajar.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Pegawai --}}
                    <div>
                        <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Pegawai / Dosen yang Studi Lanjut <span class="text-red-500">*</span>
                        </label>
                        <select name="pegawai_id" id="pegawai_id" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" @selected(old('pegawai_id', request('pegawai_id')) == $p->id)>
                                    {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }}) - {{ $p->jenis_pegawai ?? 'Pegawai' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Pengembangan & Jenjang Studi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="jenis_pengembangan" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jenis Pengembangan <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_pengembangan" id="jenis_pengembangan" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Tugas Belajar" @selected(old('jenis_pengembangan') == 'Tugas Belajar')>Tugas Belajar (Dibebastugaskan)</option>
                                <option value="Izin Belajar" @selected(old('jenis_pengembangan') == 'Izin Belajar')>Izin Belajar (Tetap Melaksanakan Tugas)</option>
                            </select>
                        </div>

                        <div>
                            <label for="jenjang_studi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Jenjang Studi <span class="text-red-500">*</span>
                            </label>
                            <select name="jenjang_studi" id="jenjang_studi" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="S2" @selected(old('jenjang_studi') == 'S2')>S2 (Magister)</option>
                                <option value="S3" @selected(old('jenjang_studi') == 'S3')>S3 (Doktor)</option>
                                <option value="Spesialis" @selected(old('jenjang_studi') == 'Spesialis')>Spesialis Keperawatan</option>
                                <option value="Subspesialis" @selected(old('jenjang_studi') == 'Subspesialis')>Subspesialis</option>
                                <option value="Post Doctoral" @selected(old('jenjang_studi') == 'Post Doctoral')>Post Doctoral</option>
                            </select>
                        </div>
                    </div>

                    {{-- Program Studi, Perguruan Tinggi, & Negara --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="program_studi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Program Studi / Peminatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="program_studi" id="program_studi" value="{{ old('program_studi') }}" required
                                   placeholder="Contoh: Ilmu Keperawatan Medikal Bedah"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="perguruan_tinggi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Perguruan Tinggi Tujuan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="perguruan_tinggi" id="perguruan_tinggi" value="{{ old('perguruan_tinggi') }}" required
                                   placeholder="Contoh: Universitas Indonesia"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="negara" class="block text-sm font-semibold text-gray-700 mb-1">
                                Negara Tujuan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="negara" id="negara" value="{{ old('negara', 'Indonesia') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Pembiayaan & Sponsor --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sumber_pembiayaan" class="block text-sm font-semibold text-gray-700 mb-1">
                                Sumber Pembiayaan <span class="text-red-500">*</span>
                            </label>
                            <select name="sumber_pembiayaan" id="sumber_pembiayaan" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Beasiswa BPI / Kemendikbud" @selected(old('sumber_pembiayaan') == 'Beasiswa BPI / Kemendikbud')>Beasiswa BPI / Kemendikbudristek</option>
                                <option value="Beasiswa LPDP" @selected(old('sumber_pembiayaan') == 'Beasiswa LPDP')>Beasiswa LPDP (Kemenkeu)</option>
                                <option value="Beasiswa Pemda" @selected(old('sumber_pembiayaan') == 'Beasiswa Pemda')>Beasiswa Pemerintah Daerah</option>
                                <option value="Mandiri / Swadana" @selected(old('sumber_pembiayaan') == 'Mandiri / Swadana')>Mandiri / Swadana</option>
                                <option value="Lainnya" @selected(old('sumber_pembiayaan') == 'Lainnya')>Lainnya (Luar Negeri / Universitas)</option>
                            </select>
                        </div>

                        <div>
                            <label for="nama_sponsor" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Sponsor / Instansi Pemberi Beasiswa
                            </label>
                            <input type="text" name="nama_sponsor" id="nama_sponsor" value="{{ old('nama_sponsor') }}"
                                   placeholder="Contoh: LPDP / Puslapdik / AAS / JASSO"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Legalitas SK Tugas Belajar --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nomor_sk" class="block text-sm font-semibold text-gray-700 mb-1">
                                Nomor SK Tugas / Izin Belajar <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_sk" id="nomor_sk" value="{{ old('nomor_sk') }}" required
                                   placeholder="Contoh: 1234/UN19/KP/2026"
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="tanggal_sk" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal SK
                            </label>
                            <input type="date" name="tanggal_sk" id="tanggal_sk" value="{{ old('tanggal_sk') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Tanggal Mulai, Tanggal Selesai, & Semester --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Mulai Studi (TMT) <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Target Tanggal Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="semester_berjalan" class="block text-sm font-semibold text-gray-700 mb-1">
                                Semester Berjalan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="semester_berjalan" id="semester_berjalan" value="{{ old('semester_berjalan', 1) }}" min="1" max="20" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Status Studi & Unggah Berkas SK / KHS --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="status_studi" class="block text-sm font-semibold text-gray-700 mb-1">
                                Status Studi <span class="text-red-500">*</span>
                            </label>
                            <select name="status_studi" id="status_studi" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Sedang Studi" @selected(old('status_studi') == 'Sedang Studi')>Sedang Studi</option>
                                <option value="Perpanjangan" @selected(old('status_studi') == 'Perpanjangan')>Perpanjangan</option>
                                <option value="Lulus" @selected(old('status_studi') == 'Lulus')>Lulus / Selesai</option>
                                <option value="Dibatalkan / DO" @selected(old('status_studi') == 'Dibatalkan / DO')>Dibatalkan / DO</option>
                            </select>
                        </div>

                        <div>
                            <label for="file_sk" class="block text-sm font-semibold text-gray-700 mb-1">
                                Berkas Scan SK Tubel (PDF)
                            </label>
                            <input type="file" name="file_sk" id="file_sk" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div>
                            <label for="file_laporan_progress" class="block text-sm font-semibold text-gray-700 mb-1">
                                Laporan Progres / KHS (PDF)
                            </label>
                            <input type="file" name="file_laporan_progress" id="file_laporan_progress" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
                            Keterangan Tambahan / Topik Riset
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                                  placeholder="Judul tesis/disertasi, nama promotor/supervisor, dsb..."
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('tugas-belajar.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            💾 Simpan Data Tugas Belajar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
