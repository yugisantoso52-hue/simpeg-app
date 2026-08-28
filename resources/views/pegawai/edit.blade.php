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
                subtitle="Perbarui dan lengkapi data profil kepegawaian secara mandiri">

                <a href="{{ $backUrl }}"
                   class="inline-flex items-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                    ← Kembali
                </a>
            </x-enterprise.page-header>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        kategori: '{{ in_array($kategori, ['dosen', 'tendik', 'phl']) ? $kategori : strtolower($pegawai->kategori_kepegawaian ?? 'dosen') }}',
        setCategory(cat) {
            this.kategori = cat;
        }
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
            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-sm">
                <div class="flex items-center justify-between px-2 mb-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori Profil:</span>
                    <span class="text-xs text-slate-400">Pilih kategori jika ingin mengubah tipe pegawai</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    
                    {{-- Tab 1: Dosen --}}
                    <button type="button" 
                            @click="setCategory('dosen')"
                            :class="kategori === 'dosen' ? 'bg-blue-50 border-blue-600 text-blue-900 ring-2 ring-blue-500/20' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border text-left transition">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                             :class="kategori === 'dosen' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-200 text-slate-700'">
                            👨‍🏫
                        </div>
                        <div>
                            <div class="font-bold text-sm">Dosen (Tenaga Pendidik)</div>
                            <div class="text-[11px] text-slate-500">Khusus Dosen, NIDN, & PAK Akademik</div>
                        </div>
                    </button>

                    {{-- Tab 2: Tendik --}}
                    <button type="button" 
                            @click="setCategory('tendik')"
                            :class="kategori === 'tendik' ? 'bg-emerald-50 border-emerald-600 text-emerald-900 ring-2 ring-emerald-500/20' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border text-left transition">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                             :class="kategori === 'tendik' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-200 text-slate-700'">
                            🧑‍💼
                        </div>
                        <div>
                            <div class="font-bold text-sm">Tenaga Kependidikan (Tendik)</div>
                            <div class="text-[11px] text-slate-500">Staf Administrasi, Laboran, & Teknisi ASN</div>
                        </div>
                    </button>

                    {{-- Tab 3: PHL --}}
                    <button type="button" 
                            @click="setCategory('phl')"
                            :class="kategori === 'phl' ? 'bg-amber-50 border-amber-600 text-amber-900 ring-2 ring-amber-500/20' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
                            class="flex items-center gap-3 p-3.5 rounded-xl border text-left transition">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                             :class="kategori === 'phl' ? 'bg-amber-600 text-white font-bold' : 'bg-slate-200 text-slate-700'">
                            👷
                        </div>
                        <div>
                            <div class="font-bold text-sm">PHL & Tenaga Kontrak</div>
                            <div class="text-[11px] text-slate-500">Pegawai Harian Lepas & Masa Kontrak Non-ASN</div>
                        </div>
                    </button>

                </div>
            </div>

            {{-- Card Formulir Utama --}}
            <x-enterprise.card>
                <div class="p-6">
                    <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="kategori" :value="kategori">

                        {{-- Include Formulir Modular --}}
                        @include('pegawai.forms._pribadi')
                        @include('pegawai.forms._kontak')
                        @include('pegawai.forms._keluarga')
                        @include('pegawai.forms._kepegawaian')
                        @include('pegawai.forms._administrasi')
                        @include('pegawai.forms._fisik')
                        
                        {{-- Include Form Riwayat Pendidikan, Diklat, Pangkat & Jabatan --}}
                        @include('pegawai.forms._pangkat')
                        @include('pegawai.forms._jabatan')
                        @include('pegawai.forms._pendidikan')
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
        document.addEventListener('DOMContentLoaded', function () {
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
                                this.value = ''; // Reset file input
                            }
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>