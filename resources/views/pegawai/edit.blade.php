<x-app-layout>

    <x-slot name="header">
        @php
            $isPegawai = auth()->user()->hasRole('pegawai') || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('pimpinan'));
            
            $backUrl = $isPegawai 
                ? route('pegawai.show', $pegawai->id) 
                : match($kategori) {
                    'dosen' => route('kepegawaian.dosen.index'),
                    'tendik' => route('kepegawaian.tendik.index'),
                    'phl' => route('kepegawaian.phl.index'),
                    default => route('pegawai.index'),
                };

            $parentLabel = $isPegawai 
                ? 'Profil Saya' 
                : match($kategori) {
                    'dosen' => 'Data Dosen',
                    'tendik' => 'Data Tendik',
                    'phl' => 'Data PHL',
                    default => 'Data Pegawai',
                };
        @endphp

        <x-enterprise.breadcrumb
            :items="[
                ['label' => $parentLabel, 'url' => $backUrl],
                ['label' => 'Edit Data: ' . ($pegawai->nama_lengkap ?? $pegawai->nama)]
            ]"
        />

        <div class="mt-4">
            <x-enterprise.page-header
                :title="'Edit Data ' . ucfirst($kategori) . ': ' . ($pegawai->nama_lengkap ?? $pegawai->nama)"
                subtitle="Perbarui dan lengkapi data profil kepegawaian secara mandiri dan terarah">

                <a href="{{ $backUrl }}"
                   class="inline-flex items-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                    ← Kembali
                </a>
            </x-enterprise.page-header>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        kategori: '{{ in_array($kategori, ['dosen', 'tendik', 'phl']) ? $kategori : strtolower($pegawai->kategori_kepegawaian ?? 'dosen') }}'
    }">
        <div class="mx-auto max-w-7xl space-y-6">

            {{-- Flash Messages --}}
            @if(session('error'))
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    {{ session('error') }}
                </x-enterprise.alert>
            @endif

            @if(session('success'))
                <x-enterprise.alert type="success" title="Sukses">
                    {{ session('success') }}
                </x-enterprise.alert>
            @endif

            @if($errors->any())
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-enterprise.alert>
            @endif

            {{-- ========================================================================= --}}
            {{-- TAB STATUS KATEGORI PEGAWAI                                              --}}
            {{-- ========================================================================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between px-1 mb-3">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kategori Profil:</span>
                    <span class="text-xs text-blue-600 font-medium">Klik untuk menyesuaikan jenis profil pegawai</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    
                    {{-- Tab 1: Dosen --}}
                    <button type="button" 
                            id="btn-cat-dosen"
                            onclick="applyCategory('dosen')"
                            @click="kategori = 'dosen'"
                            class="cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm">
                        <div class="cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0">
                            👨‍🏫
                        </div>
                        <div>
                            <div class="cat-title font-bold text-sm">Dosen (Tenaga Pendidik)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Khusus Dosen, NIDN, & PAK Akademik</div>
                        </div>
                    </button>

                    {{-- Tab 2: Tendik --}}
                    <button type="button" 
                            id="btn-cat-tendik"
                            onclick="applyCategory('tendik')"
                            @click="kategori = 'tendik'"
                            class="cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm">
                        <div class="cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0">
                            🧑‍💼
                        </div>
                        <div>
                            <div class="cat-title font-bold text-sm">Tenaga Kependidikan (Tendik)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Staf Administrasi, Laboran, & Teknisi ASN</div>
                        </div>
                    </button>

                    {{-- Tab 3: PHL --}}
                    <button type="button" 
                            id="btn-cat-phl"
                            onclick="applyCategory('phl')"
                            @click="kategori = 'phl'"
                            class="cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm">
                        <div class="cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0">
                            👷
                        </div>
                        <div>
                            <div class="cat-title font-bold text-sm">PHL & Tenaga Kontrak</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Pegawai Harian Lepas & Kontrak Non-ASN</div>
                        </div>
                    </button>

                </div>

                {{-- Status Banner Aktif --}}
                <div id="category-status-banner" class="mt-3.5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2">
                    <span id="banner-icon">ℹ️</span>
                    <span id="banner-text">Memuat profil...</span>
                </div>
            </div>

            {{-- Card Formulir Utama --}}
            <x-enterprise.card>
                <div class="p-6">
                    <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="kategori" id="input_kategori" value="{{ in_array($kategori, ['dosen', 'tendik', 'phl']) ? $kategori : strtolower($pegawai->kategori_kepegawaian ?? 'dosen') }}">

                        {{-- URUTAN STANDAR SESUAI SISTEM --}}
                        {{-- 1. DATA PRIBADI --}}
                        @include('pegawai.forms._pribadi')

                        {{-- 2. INFORMASI KONTAK & DOMISILI --}}
                        @include('pegawai.forms._kontak')

                        {{-- 3. DATA KELUARGA --}}
                        @include('pegawai.forms._keluarga')

                        {{-- 4. DATA KEPEGAWAIAN & JABATAN --}}
                        @include('pegawai.forms._kepegawaian')

                        {{-- 5. ADMINISTRASI KEPEGAWAIAN & LEGALITAS --}}
                        @include('pegawai.forms._administrasi')

                        {{-- 6. DATA FISIK & KESEHATAN --}}
                        @include('pegawai.forms._fisik')
                        
                        {{-- 7. RIWAYAT PANGKAT / GOLONGAN --}}
                        @include('pegawai.forms._pangkat')

                        {{-- 8. RIWAYAT JABATAN --}}
                        @include('pegawai.forms._jabatan')

                        {{-- 9. RIWAYAT PENDIDIKAN --}}
                        @include('pegawai.forms._pendidikan')

                        {{-- 10. RIWAYAT DIKLAT / PELATIHAN --}}
                        @include('pegawai.forms._diklat')

                        <x-enterprise.forms.actions
                            :back="$backUrl"
                            submit="Simpan Perubahan"/>
                    </form>
                </div>
            </x-enterprise.card>

        </div>
    </div>

    <script>
        function applyCategory(cat) {
            const inputKategori = document.getElementById('input_kategori');
            if (inputKategori) inputKategori.value = cat;

            const btnDosen = document.getElementById('btn-cat-dosen');
            const btnTendik = document.getElementById('btn-cat-tendik');
            const btnPhl = document.getElementById('btn-cat-phl');
            const banner = document.getElementById('category-status-banner');
            const bannerIcon = document.getElementById('banner-icon');
            const bannerText = document.getElementById('banner-text');

            const defaultClasses = 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300';
            [btnDosen, btnTendik, btnPhl].forEach(btn => {
                if (btn) {
                    btn.className = 'cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm ' + defaultClasses;
                    const iconBox = btn.querySelector('.cat-icon-box');
                    if (iconBox) iconBox.className = 'cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-slate-200 text-slate-700';
                }
            });

            if (cat === 'dosen') {
                if (btnDosen) {
                    btnDosen.className = 'cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm bg-blue-50 border-blue-600 text-blue-900 ring-2 ring-blue-500/20';
                    const iconBox = btnDosen.querySelector('.cat-icon-box');
                    if (iconBox) iconBox.className = 'cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-blue-600 text-white font-bold';
                }
                if (banner) {
                    banner.className = 'mt-3.5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2 bg-blue-50 border-blue-200 text-blue-900';
                    bannerIcon.textContent = '👨‍🏫';
                    bannerText.textContent = 'Mode Aktif: Profil Dosen (Tenaga Pendidik) — Kolom NIDN, Jabatan Fungsional Akademik, dan Angka Kredit PAK ditampilkan.';
                }
            } else if (cat === 'tendik') {
                if (btnTendik) {
                    btnTendik.className = 'cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm bg-emerald-50 border-emerald-600 text-emerald-900 ring-2 ring-emerald-500/20';
                    const iconBox = btnTendik.querySelector('.cat-icon-box');
                    if (iconBox) iconBox.className = 'cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-emerald-600 text-white font-bold';
                }
                if (banner) {
                    banner.className = 'mt-3.5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2 bg-emerald-50 border-emerald-200 text-emerald-900';
                    bannerIcon.textContent = '🧑‍💼';
                    bannerText.textContent = 'Mode Aktif: Profil Tenaga Kependidikan (Tendik) — Kolom NIP, Pangkat/Golongan ASN, TMT KGB/KP, dan Satyalancana ditampilkan.';
                }
            } else if (cat === 'phl') {
                if (btnPhl) {
                    btnPhl.className = 'cat-tab-btn flex items-center gap-3.5 p-4 rounded-xl border-2 text-left cursor-pointer transition transform active:scale-98 shadow-sm bg-amber-50 border-amber-600 text-amber-900 ring-2 ring-amber-500/20';
                    const iconBox = btnPhl.querySelector('.cat-icon-box');
                    if (iconBox) iconBox.className = 'cat-icon-box w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 bg-amber-600 text-white font-bold';
                }
                if (banner) {
                    banner.className = 'mt-3.5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2 bg-amber-50 border-amber-200 text-amber-900';
                    bannerIcon.textContent = '👷';
                    bannerText.textContent = 'Mode Aktif: Profil PHL & Tenaga Kontrak — Kolom NIK, Unit Kerja, dan Masa Kontrak Kerja ditampilkan.';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const initialCat = '{{ in_array($kategori, ['dosen', 'tendik', 'phl']) ? $kategori : strtolower($pegawai->kategori_kepegawaian ?? 'dosen') }}';
            applyCategory(initialCat);

            const form = document.querySelector('form');
            if (form) {
                const fileInputs = form.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        if (this.files && this.files[0]) {
                            const file = this.files[0];
                            const maxSize = 10 * 1024 * 1024; // 10MB
                            if (file.size > maxSize) {
                                alert(`Ukuran berkas "${file.name}" (${(file.size / (1024 * 1024)).toFixed(2)} MB) melebihi batas maksimal 10 MB. Silakan pilih berkas yang lebih kecil.`);
                                this.value = '';
                            }
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>