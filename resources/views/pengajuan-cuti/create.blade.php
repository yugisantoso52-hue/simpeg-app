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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Panel Ringkasan Pemisahan Hak, Syarat & Ketentuan Cuti PNS vs PPPK --}}
            @include('pengajuan-cuti._ringkasan_regulasi_bkn', ['defaultOpen' => false])

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

                @php
                    $tahunBerjalan = (int) now()->year;
                    $isPemohonPppk = $pegawai ? $pegawai->isPppk() : false;
                    $statusAsnLabel = $pegawai ? $pegawai->status_asn_cuti : 'PNS';
                    $catatanCutiPemohon = $pegawai
                        ? $pegawai->getCatatanCutiTahunan($tahunBerjalan)
                        : [
                            'N'   => ['kode' => 'N',   'tahun' => $tahunBerjalan,     'label_tahun' => "{$tahunBerjalan} (N)",         'sisa' => 12,   'sisa_formatted' => '12 Hari', 'keterangan' => 'Hak Cuti Tahun Berjalan (N)'],
                            'N-1' => ['kode' => 'N-1', 'tahun' => $tahunBerjalan - 1, 'label_tahun' => ($tahunBerjalan - 1) . ' (N-1)', 'sisa' => null, 'sisa_formatted' => '-',       'keterangan' => 'Sisa Cuti 1 Tahun Sebelumnya (N-1)'],
                            'N-2' => ['kode' => 'N-2', 'tahun' => $tahunBerjalan - 2, 'label_tahun' => ($tahunBerjalan - 2) . ' (N-2)', 'sisa' => null, 'sisa_formatted' => '-',       'keterangan' => 'Sisa Cuti 2 Tahun Lalu (N-2)'],
                        ];
                @endphp

                <form action="{{ route('pengajuan-cuti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                      x-data="{
                          jenisCuti: '{{ old('jenis_cuti', 'Cuti Tahunan') }}',
                          tglMulai: '{{ old('tanggal_mulai') }}',
                          tglSelesai: '{{ old('tanggal_selesai') }}',
                          isPppk: {{ $isPemohonPppk ? 'true' : 'false' }},
                          statusAsnLabel: '{{ $statusAsnLabel }}',
                          sisaCuti: {{ $catatanCutiPemohon['N']['sisa'] ?? 12 }},
                          sisaNFormatted: '{{ $catatanCutiPemohon['N']['sisa_formatted'] }}',
                          sisaN1Formatted: '{{ $catatanCutiPemohon['N-1']['sisa_formatted'] }}',
                          sisaN2Formatted: '{{ $catatanCutiPemohon['N-2']['sisa_formatted'] }}',
                          updatePegawaiQuota(event) {
                              let opt = event.target.selectedOptions[0];
                              if (opt && opt.dataset.sisaN !== undefined) {
                                  this.sisaCuti = parseInt(opt.dataset.sisaN, 10) || 12;
                                  this.sisaNFormatted = opt.dataset.sisaNFormatted || (this.sisaCuti + ' Hari');
                                  this.sisaN1Formatted = opt.dataset.sisaN1Formatted || '-';
                                  this.sisaN2Formatted = opt.dataset.sisaN2Formatted || '-';
                                  this.isPppk = opt.dataset.isPppk === '1';
                                  this.statusAsnLabel = opt.dataset.statusAsn || (this.isPppk ? 'PPPK' : 'PNS');
                                  if (this.isPppk && ['Cuti Alasan Penting', 'Cuti Besar', 'Cuti di Luar Tanggungan Negara'].includes(this.jenisCuti)) {
                                      this.jenisCuti = 'Cuti Tahunan';
                                  }
                              }
                          },
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
                    @if(Auth::user()->hasRole('admin'))
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="pegawai_id" class="block text-sm font-semibold text-gray-700">
                                    Pilih Pegawai yang Mengajukan Cuti <span class="text-red-500">*</span>
                                </label>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                      :class="isPppk ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                      x-text="isPppk ? 'Status: PPPK (4 Hak Cuti - Perbkn No. 7/2022)' : 'Status: PNS (7 Hak Cuti ASN)'">
                                </span>
                            </div>
                            <select name="pegawai_id" id="pegawai_id" required
                                    @change="updatePegawaiQuota($event)"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach($pegawaiList as $p)
                                    @php
                                        $catatanP = $p->getCatatanCutiTahunan($tahunBerjalan);
                                    @endphp
                                    <option value="{{ $p->id }}"
                                            data-sisa-n="{{ $catatanP['N']['sisa'] }}"
                                            data-sisa-n-formatted="{{ $catatanP['N']['sisa_formatted'] }}"
                                            data-sisa-n1-formatted="{{ $catatanP['N-1']['sisa_formatted'] }}"
                                            data-sisa-n2-formatted="{{ $catatanP['N-2']['sisa_formatted'] }}"
                                            data-is-pppk="{{ $p->isPppk() ? '1' : '0' }}"
                                            data-status-asn="{{ $p->status_asn_cuti }}"
                                            @selected(old('pegawai_id') == $p->id)>
                                        [{{ $p->status_asn_cuti }}] {{ $p->nama_lengkap ?? $p->nama }} (NIP: {{ $p->nip ?? '-' }}) - Sisa Cuti: {{ $p->sisa_cuti_tahunan }} Hari
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Info Pemohon --}}
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 uppercase font-semibold">Nama Pemohon Cuti</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $isPemohonPppk ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $statusAsnLabel }} — {{ $isPemohonPppk ? 'Peraturan BKN No. 7 Tahun 2022' : 'Skema Hak Cuti PNS' }}
                                    </span>
                                </div>
                                <div class="text-base font-bold text-gray-900 mt-0.5">{{ optional($pegawai)->nama_lengkap ?? optional($pegawai)->nama ?? Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">NIP. {{ optional($pegawai)->nip ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 uppercase font-semibold">Sisa Kuota Cuti Tahunan</div>
                                <div class="text-xl font-extrabold text-emerald-600">{{ optional($pegawai)->sisa_cuti_tahunan ?? 12 }} Hari Kerja</div>
                            </div>
                        </div>
                    @endif

                    {{-- V. Catatan Cuti Tahunan (N, N-1, N-2 sesuai Peraturan BKN Nomor 7 Tahun 2022) --}}
                    <div class="rounded-lg border border-gray-200 overflow-hidden bg-white">
                        <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-700">
                                V. Catatan Cuti Tahunan (Peraturan BKN Nomor 7 Tahun 2022)
                            </span>
                            <span class="text-[11px] text-gray-500">N = Tahun Berjalan | N-1 = 1 Tahun Sebelumnya | N-2 = 2 Tahun Lalu</span>
                        </div>
                        <table class="w-full text-xs text-left text-gray-700">
                            <thead class="bg-gray-50/60 text-gray-600 uppercase border-b border-gray-200">
                                <tr>
                                    <th class="py-2 px-4 text-center w-28">Tahun</th>
                                    <th class="py-2 px-4 text-center w-32">Sisa Kuota</th>
                                    <th class="py-2 px-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="py-2 px-4 text-center font-semibold">{{ $catatanCutiPemohon['N']['label_tahun'] }}</td>
                                    <td class="py-2 px-4 text-center font-bold text-emerald-700" x-text="sisaNFormatted">{{ $catatanCutiPemohon['N']['sisa_formatted'] }}</td>
                                    <td class="py-2 px-4">{{ $catatanCutiPemohon['N']['keterangan'] }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 text-center font-medium text-gray-600">{{ $catatanCutiPemohon['N-1']['label_tahun'] }}</td>
                                    <td class="py-2 px-4 text-center font-semibold text-gray-600" x-text="sisaN1Formatted">{{ $catatanCutiPemohon['N-1']['sisa_formatted'] }}</td>
                                    <td class="py-2 px-4 text-gray-600">{{ $catatanCutiPemohon['N-1']['keterangan'] }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 text-center font-medium text-gray-600">{{ $catatanCutiPemohon['N-2']['label_tahun'] }}</td>
                                    <td class="py-2 px-4 text-center font-semibold text-gray-600" x-text="sisaN2Formatted">{{ $catatanCutiPemohon['N-2']['sisa_formatted'] }}</td>
                                    <td class="py-2 px-4 text-gray-600">{{ $catatanCutiPemohon['N-2']['keterangan'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Jenis Cuti (Dipisahkan antara Hak Cuti PNS dan PPPK) --}}
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label for="jenis_cuti" class="block text-sm font-semibold text-gray-700">
                                Jenis Cuti <span class="text-red-500">*</span>
                            </label>
                            <span x-show="isPppk" class="text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded">
                                ⚖️ Pemohon PPPK: Hanya Cuti Tahunan, Cuti Sakit &amp; Cuti Melahirkan (Pasal 4 Perbkn No. 7/2022)
                            </span>
                        </div>
                        <select name="jenis_cuti" id="jenis_cuti" x-model="jenisCuti" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="Cuti Tahunan">1. Cuti Tahunan (Maks. 12 Hari Kerja / Pasal 9 PPPK Maks. 6 Hari Kerja)</option>
                            <option value="Cuti Sakit">2. Cuti Sakit (Wajib Surat Dokter jika &gt; 1 hari)</option>
                            <option value="Cuti Melahirkan">3. Cuti Melahirkan (Maks. 3 Bulan — Anak ke-1 s.d. ke-3)</option>
                            <option value="Cuti Alasan Penting" :disabled="isPppk" x-text="isPppk ? '4. Cuti Karena Alasan Penting (Khusus PNS — PPPK gunakan Cuti Tahunan Pasal 9)' : '4. Cuti Karena Alasan Penting (CAP - Khusus PNS)'">4. Cuti Karena Alasan Penting</option>
                            <option value="Cuti Besar" :disabled="isPppk" x-text="isPppk ? '5. Cuti Besar (Khusus PNS — Tidak berlaku bagi PPPK)' : '5. Cuti Besar (Khusus PNS — Masa Kerja ≥ 5 Tahun)'">5. Cuti Besar (Masa Kerja &gt; 5 Tahun)</option>
                            <option value="Cuti di Luar Tanggungan Negara" :disabled="isPppk" x-text="isPppk ? '6. Cuti di Luar Tanggungan Negara / CLTN (Khusus PNS — Tidak berlaku bagi PPPK)' : '6. Cuti di Luar Tanggungan Negara (CLTN - Khusus PNS)'">6. Cuti di Luar Tanggungan Negara (CLTN)</option>
                        </select>

                        {{-- Kotak Panduan Syarat & Ketentuan Dinamis Sesuai Jenis Cuti Terpilih --}}
                        <div class="p-3.5 rounded-xl border text-xs leading-relaxed"
                             :class="isPppk ? 'bg-amber-50/70 border-amber-200 text-amber-950' : 'bg-blue-50/60 border-blue-200 text-blue-950'">
                            <template x-if="jenisCuti === 'Cuti Tahunan'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>📌</span>
                                        <span x-text="isPppk ? 'Syarat & Ketentuan Cuti Tahunan PPPK (Pasal 5 s.d. Pasal 11 Perbkn No. 7 Tahun 2022):' : 'Syarat & Ketentuan Cuti Tahunan PNS:'"></span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Hak kuota dasar <strong>12 hari kerja</strong> setelah bekerja paling sedikit 1 tahun secara terus-menerus.</li>
                                        <li>Akumulasi sisa cuti: maksimal <strong>18 hari kerja</strong> (1 tahun tidak diambil) atau <strong>24 hari kerja</strong> (2 tahun berturut-turut tidak diambil; bagi PPPK wajib masa perjanjian kerja &gt; 2 atau &gt; 3 tahun).</li>
                                        <li x-show="isPppk"><strong>Ketentuan Khusus PPPK (Pasal 9)</strong>: Bagi PPPK yang masa kerjanya belum 1 tahun atau mengalami alasan mendesak (ibu/bapak/istri/suami/anak/mertua sakit keras atau meninggal dunia, atau melangsungkan perkawinan pertama), diberikan Cuti Tahunan paling lama <strong>6 hari kerja</strong> (wajib lampirkan Surat Rawat Inap jika sakit keras).</li>
                                    </ul>
                                </div>
                            </template>

                            <template x-if="jenisCuti === 'Cuti Sakit'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>🏥</span>
                                        <span x-text="isPppk ? 'Syarat & Ketentuan Cuti Sakit PPPK (Pasal 12 s.d. Pasal 17 Perbkn No. 7 Tahun 2022):' : 'Syarat & Ketentuan Cuti Sakit PNS:'"></span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Sakit 1 hari: menyampaikan surat keterangan sakit tertulis kepada atasan langsung dengan surat dokter.</li>
                                        <li x-show="isPppk">Sakit 2 s.d. 14 hari: permohonan tertulis kepada PYBMC dengan melampirkan surat keterangan dokter.</li>
                                        <li x-show="isPppk">Sakit &gt; 14 hari: diberikan paling lama <strong>1 (satu) bulan</strong> dan wajib melampirkan <strong>Surat Keterangan Dokter Pemerintah (Dokter PNS / Faskes Pemerintah)</strong> (Pasal 13).</li>
                                        <li x-show="!isPppk">Bagi PNS: Sakit &gt; 14 hari diberikan s.d. 1 tahun dan dapat ditambah maks. 6 bulan berdasarkan surat Tim Penguji Kesehatan.</li>
                                        <li>Mengalami gugur kandungan: berhak atas cuti sakit paling lama <strong>1,5 (satu setengah) bulan</strong> dengan surat keterangan dokter/bidan.</li>
                                    </ul>
                                </div>
                            </template>

                            <template x-if="jenisCuti === 'Cuti Melahirkan'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>👶</span>
                                        <span x-text="isPppk ? 'Syarat & Ketentuan Cuti Melahirkan PPPK (Pasal 18 s.d. Pasal 19 Perbkn No. 7 Tahun 2022):' : 'Syarat & Ketentuan Cuti Melahirkan PNS:'"></span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Berlaku untuk kelahiran <strong>anak pertama sampai dengan anak ketiga</strong> (bagi PPPK dihitung sejak saat berstatus PPPK).</li>
                                        <li>Lamanya hak atas cuti melahirkan diberikan paling lama <strong>3 (tiga) bulan</strong> dengan tetap menerima penghasilan sesuai ketentuan.</li>
                                        <li>Melampirkan Surat Keterangan HPL / Rujukan Persalinan dari Dokter Spesialis atau Bidan.</li>
                                    </ul>
                                </div>
                            </template>

                            <template x-if="jenisCuti === 'Cuti Alasan Penting'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>⚡</span>
                                        <span>Syarat &amp; Ketentuan Cuti Karena Alasan Penting (CAP — Khusus PNS):</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Hanya berlaku bagi <strong>PNS</strong> (paling lama 1 bulan) untuk: keluarga inti sakit keras/meninggal dunia, perkawinan, istri melahirkan/operasi caesar, atau mengalami musibah kebakaran/bencana alam.</li>
                                        <li>Bagi <strong>PPPK</strong>, alasan tersebut diajukan melalui <strong>Cuti Tahunan Pasal 9 (maks. 6 hari kerja)</strong>.</li>
                                    </ul>
                                </div>
                            </template>

                            <template x-if="jenisCuti === 'Cuti Besar'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>🕋</span>
                                        <span>Syarat &amp; Ketentuan Cuti Besar (Khusus PNS):</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Hanya berlaku bagi <strong>PNS</strong> yang telah bekerja paling singkat <strong>5 tahun secara terus-menerus</strong>, diberikan paling lama <strong>3 bulan</strong> (atau untuk ibadah haji pertama / kelahiran anak ke-4).</li>
                                        <li>PNS yang menggunakan Cuti Besar tidak berhak lagi atas Cuti Tahunan dalam tahun yang bersangkutan.</li>
                                    </ul>
                                </div>
                            </template>

                            <template x-if="jenisCuti === 'Cuti di Luar Tanggungan Negara'">
                                <div>
                                    <div class="font-bold mb-1 flex items-center gap-1.5">
                                        <span>🏛️</span>
                                        <span>Syarat &amp; Ketentuan Cuti di Luar Tanggungan Negara (CLTN — Khusus PNS):</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                                        <li>Hanya berlaku bagi <strong>PNS</strong> yang telah bekerja paling singkat <strong>5 tahun terus-menerus</strong> karena alasan pribadi dan mendesak (paling lama 3 tahun), dengan persetujuan teknis Kepala BKN.</li>
                                    </ul>
                                </div>
                            </template>
                        </div>
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
