<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏖️</span> Form Permohonan Cuti
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pengajuan Cuti Tahunan, Sakit, Melahirkan, Alasan Penting, atau Cuti Besar
                </p>
            </div>
            <a href="{{ route('pengajuan-cuti.index') }}"
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

                <form action="{{ route('pengajuan-cuti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                      x-data="{
                          jenisCuti: '{{ old('jenis_cuti', 'Cuti Tahunan') }}',
                          tglMulai: '{{ old('tanggal_mulai') }}',
                          tglSelesai: '{{ old('tanggal_selesai') }}',
                          sisaCuti: {{ $pegawai ? $pegawai->sisa_cuti_tahunan : 12 }},
                          get durasiHari() {
                              if (!this.tglMulai || !this.tglSelesai) return 0;
                              let start = new Date(this.tglMulai);
                              let end = new Date(this.tglSelesai);
                              if (start > end) return 0;
                              let count = 0;
                              let cur = new Date(start);
                              while (cur <= end) {
                                  let day = cur.getDay();
                                  if (day !== 0 && day !== 6) count++;
                                  cur.setDate(cur.getDate() + 1);
                              }
                              return count === 0 ? 1 : count;
                          }
                      }">
                    @csrf

                    {{-- Jika Admin: Bisa memilih pegawai --}}
                    @if(Auth::user()->hasRole('admin') && !$isPegawaiOnly)
                        <div>
                            <label for="pegawai_id" class="block text-sm font-semibold text-gray-700 mb-1">
                                Pilih Pegawai yang Mengajukan Cuti <span class="text-red-500">*</span>
                            </label>
                            <select name="pegawai_id" id="pegawai_id" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($pegawaiList as $p)
                                    <option value="{{ $p->id }}" @selected(old('pegawai_id') == $p->id)>
                                        {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }}) - Sisa Cuti: {{ $p->sisa_cuti_tahunan }} Hari
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Info Pemohon --}}
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500 uppercase font-semibold">Nama Pemohon Cuti</div>
                                <div class="text-base font-bold text-gray-900">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</div>
                                <div class="text-xs text-gray-500 font-mono">NIP. {{ $pegawai->nip ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 uppercase font-semibold">Sisa Kuota Cuti Tahunan</div>
                                <div class="text-xl font-extrabold text-emerald-600">{{ $pegawai->sisa_cuti_tahunan }} Hari Kerja</div>
                            </div>
                        </div>
                    @endif

                    {{-- Jenis Cuti --}}
                    <div>
                        <label for="jenis_cuti" class="block text-sm font-semibold text-gray-700 mb-1">
                            Jenis Cuti <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_cuti" id="jenis_cuti" x-model="jenisCuti" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="Cuti Tahunan">1. Cuti Tahunan (Maks 12 Hari Kerja)</option>
                            <option value="Cuti Sakit">2. Cuti Sakit (Wajib Surat Dokter jika > 1 hari)</option>
                            <option value="Cuti Melahirkan">3. Cuti Melahirkan (Maks 3 Bulan)</option>
                            <option value="Cuti Alasan Penting">4. Cuti Karena Alasan Penting</option>
                            <option value="Cuti Besar">5. Cuti Besar (Masa Kerja > 5 Tahun)</option>
                            <option value="Cuti di Luar Tanggungan Negara">6. Cuti di Luar Tanggungan Negara (CLTN)</option>
                        </select>
                    </div>

                    {{-- Tanggal Mulai & Tanggal Selesai --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tanggal_mulai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Mulai Cuti <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" x-model="tglMulai" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-semibold text-gray-700 mb-1">
                                Tanggal Selesai Cuti <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" x-model="tglSelesai" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    {{-- Estimasi Hari & Peringatan Kuota --}}
                    <div x-show="tglMulai && tglSelesai" class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">⏱️</span>
                            <div class="text-xs text-emerald-900">
                                Estimasi Durasi: <strong class="text-sm font-bold" x-text="durasiHari + ' Hari Kerja'"></strong> (Senin s.d. Jumat).
                            </div>
                        </div>
                        <template x-if="jenisCuti === 'Cuti Tahunan' && durasiHari > sisaCuti">
                            <div class="text-xs font-bold text-red-600 bg-red-100 px-3 py-1 rounded">
                                ⚠️ Melebihi sisa kuota (<span x-text="sisaCuti"></span> hari)
                            </div>
                        </template>
                    </div>

                    {{-- Alasan Cuti --}}
                    <div>
                        <label for="alasan" class="block text-sm font-semibold text-gray-700 mb-1">
                            Alasan Permohonan Cuti <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan" id="alasan" rows="3" required
                                  placeholder="Contoh: Keperluan keluarga di luar kota / Menjalani persalinan anak kedua..."
                                  class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('alasan') }}</textarea>
                    </div>

                    {{-- Alamat & Nomor Telepon Selama Cuti --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="alamat_selama_cuti" class="block text-sm font-semibold text-gray-700 mb-1">
                                Alamat Selama Menjalankan Cuti
                            </label>
                            <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" value="{{ old('alamat_selama_cuti') }}"
                                   placeholder="Contoh: Jl. Sudirman No. 123, Pekanbaru"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="nomor_telepon" class="block text-sm font-semibold text-gray-700 mb-1">
                                No. Telepon / WhatsApp Aktif
                            </label>
                            <input type="text" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon') }}"
                                   placeholder="Contoh: 081234567890"
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    {{-- Lampiran Berkas Pendukung (Surat Dokter, dsb.) --}}
                    <div>
                        <label for="file_lampiran" class="block text-sm font-semibold text-gray-700 mb-1">
                            Unggah Lampiran Berkas Pendukung
                        </label>
                        <input type="file" name="file_lampiran" id="file_lampiran" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <span class="text-[11px] text-gray-500 mt-1 block">Wajib untuk Cuti Sakit > 1 hari (Surat Keterangan Dokter). Format: PDF/JPG/PNG (Maks 4MB).</span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('pengajuan-cuti.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            📤 Ajukan Permohonan Cuti
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
