<x-app-layout>
    <x-slot name="header">
        <x-enterprise.breadcrumb
            :items="[
                ['label' => 'Layanan Kepegawaian', 'url' => '#'],
                ['label' => 'Verifikasi & Pengajuan Jabatan Struktural']
            ]"
        />

        <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <x-enterprise.page-header
                title="Pengajuan & Verifikasi Jabatan Struktural"
                subtitle="Penetapan jabatan berbasis verifikasi berkas SK resmi oleh Administrator & Ka. Pokja Kepegawaian">
            </x-enterprise.page-header>

            <button type="button" onclick="document.getElementById('modal-ajukan-jabatan').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <span>➕</span> Ajukan Jabatan Baru (SK)
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-3 sm:px-6 lg:px-8 xl:px-10">

            @if(session('success'))
                <x-enterprise.alert type="success" title="Sukses">
                    {{ session('success') }}
                </x-enterprise.alert>
            @endif

            @if(session('error'))
                <x-enterprise.alert type="error" title="Perhatian">
                    {{ session('error') }}
                </x-enterprise.alert>
            @endif

            @if($errors->any())
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    <ul class="list-disc list-inside text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </x-enterprise.alert>
            @endif

            {{-- Kartu Petunjuk --}}
            <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl flex items-start gap-3">
                <span class="text-2xl">🏛️</span>
                <div class="text-xs text-amber-900 leading-relaxed">
                    <strong class="font-bold text-amber-950 block text-sm mb-1">Mekanisme Mandiri Berbasis Verifikasi SK:</strong>
                    Pegawai (Dosen maupun Tendik) dapat mengusulkan jabatan struktural (Dekan, Wakil Dekan, Ketua Jurusan, Koordinator Prodi, Kabag Umum, Ka. Pokja, dll) dengan melampirkan SK Penetapan. 
                    Data ini akan diverifikasi oleh <strong>Administrator</strong> atau <strong>Ka. Pokja Kepegawaian & Keuangan</strong>. Setelah disetujui, posisi pada <strong>Bagan Struktur Organisasi & Peta Jabatan</strong> otomatis terisi dan hak approval langsung aktif.
                </div>
            </div>

            {{-- Tabel Daftar Pengajuan --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <h3 class="font-bold text-gray-800 text-base">Daftar Pengajuan Jabatan</h3>
                        @if($canVerify)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-500">Filter Status:</span>
                                <a href="{{ route('pengajuan-jabatan.index', ['status' => 'menunggu']) }}" class="px-2.5 py-1 rounded-lg font-bold {{ request('status', 'menunggu') == 'menunggu' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">Menunggu</a>
                                <a href="{{ route('pengajuan-jabatan.index', ['status' => 'disetujui']) }}" class="px-2.5 py-1 rounded-lg font-bold {{ request('status') == 'disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">Disetujui</a>
                                <a href="{{ route('pengajuan-jabatan.index', ['status' => 'ditolak']) }}" class="px-2.5 py-1 rounded-lg font-bold {{ request('status') == 'ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-600' }}">Ditolak</a>
                                <a href="{{ route('pengajuan-jabatan.index', ['status' => 'semua']) }}" class="px-2.5 py-1 rounded-lg font-bold {{ request('status') == 'semua' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">Semua</a>
                            </div>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-3 text-left">Pegawai</th>
                                    <th class="px-4 py-3 text-left">Jabatan Diusulkan</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja</th>
                                    <th class="px-4 py-3 text-left">No. & TMT SK</th>
                                    <th class="px-4 py-3 text-center">Berkas SK</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    @if($canVerify)
                                        <th class="px-4 py-3 text-center w-36">Aksi Verifikasi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($pengajuans as $item)
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900">{{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama ?? '-' }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">NIP: {{ $item->pegawai->nip ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">
                                            {{ $item->jabatan->nama_jabatan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $item->unitKerja->nama_unit ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-800">{{ $item->nomor_sk }}</div>
                                            <div class="text-[11px] text-gray-500">TMT: {{ $item->tmt_jabatan?->format('d/m/Y') ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item->file_sk)
                                                <a href="{{ route('document.preview', ['path' => $item->file_sk]) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold underline">
                                                    📄 Lihat SK
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item->status === 'disetujui')
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                    ✓ Disetujui
                                                </span>
                                            @elseif($item->status === 'ditolak')
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                                    ✕ Ditolak
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                    ⏳ Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        @if($canVerify)
                                            <td class="px-4 py-3 text-center">
                                                @if($item->status === 'menunggu')
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <form action="{{ route('pengajuan-jabatan.verifikasi', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui pengajuan jabatan ini? Jabatan dan struktur organisasi akan langsung terisi.');">
                                                            @csrf
                                                            <input type="hidden" name="aksi" value="setujui">
                                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-xs transition">
                                                                Setujui
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('pengajuan-jabatan.verifikasi', $item->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan jabatan ini?');">
                                                            @csrf
                                                            <input type="hidden" name="aksi" value="tolak">
                                                            <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-xs transition">
                                                                Tolak
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-[11px]">Selesai diproses</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canVerify ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada pengajuan jabatan dalam daftar ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pengajuans->links() }}
                    </div>
                </div>
            </x-enterprise.card>

        </div>
    </div>

    {{-- MODAL AJUKAN JABATAN BARU --}}
    <div id="modal-ajukan-jabatan" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 relative">
            <button onclick="document.getElementById('modal-ajukan-jabatan').classList.add('hidden')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold">
                ✕
            </button>

            <h3 class="text-lg font-bold text-gray-900 mb-1">Pengajuan Jabatan Struktural Baru</h3>
            <p class="text-xs text-gray-500 mb-4">Isi data sesuai SK Penetapan / Pengangkatan Jabatan resmi.</p>

            <form action="{{ route('pengajuan-jabatan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Pilih Jabatan Struktural *</label>
                    <select name="jabatan_id" required class="w-full rounded-xl border border-gray-300 p-2.5 text-xs">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatanList as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jabatan }} @if($j->kelas_jabatan)(Grade {{ $j->kelas_jabatan }})@endif</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Unit Kerja Penempatan *</label>
                    <select name="unit_kerja_id" required class="w-full rounded-xl border border-gray-300 p-2.5 text-xs">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitKerjaList as $u)
                            <option value="{{ $u->id }}">{{ $u->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nomor SK *</label>
                        <input type="text" name="nomor_sk" required placeholder="Contoh: UN19/KP/2026" class="w-full rounded-xl border border-gray-300 p-2.5 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tanggal SK *</label>
                        <input type="date" name="tanggal_sk" required class="w-full rounded-xl border border-gray-300 p-2.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">TMT Jabatan (Terhitung Mulai Tanggal) *</label>
                    <input type="date" name="tmt_jabatan" required class="w-full rounded-xl border border-gray-300 p-2.5 text-xs">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Unggah Berkas SK (PDF/Gambar Max 10MB) *</label>
                    <input type="file" name="file_sk" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Keterangan tambahan jika ada..." class="w-full rounded-xl border border-gray-300 p-2.5 text-xs"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-ajukan-jabatan').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-xs transition">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
