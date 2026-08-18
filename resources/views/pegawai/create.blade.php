<x-app-layout>

    <x-slot name="header">
        <x-enterprise.breadcrumb
            :items="[
                ['label'=>'Pegawai','url'=>route('pegawai.index')],
                ['label'=>'Tambah Pegawai']
            ]"
        />

        <div class="mt-4">
            <x-enterprise.page-header
                title="Tambah Pegawai"
                subtitle="Tambah data pegawai baru SIKAP Enterprise">

                <a href="{{ route('pegawai.index') }}"
                   class="inline-flex items-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition">
                    ← Kembali
                </a>
            </x-enterprise.page-header>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6">

            @if($errors->any())
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-enterprise.alert>
            @endif

            <x-enterprise.card>
                <div class="p-6">
                    <form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @include('pegawai.forms._pribadi')
                        @include('pegawai.forms._kontak')
                        @include('pegawai.forms._keluarga')
                        @include('pegawai.forms._kepegawaian')
                        @include('pegawai.forms._administrasi')
                        @include('pegawai.forms._fisik')
                        
                        {{-- Include Form Riwayat Pendidikan & Diklat --}}
                        @include('pegawai.forms._pendidikan')
                        @include('pegawai.forms._diklat')

                        <x-enterprise.forms.actions
                            :back="route('pegawai.index')"
                            submit="Simpan Pegawai"/>
                    </form>
                </div>
            </x-enterprise.card>

        </div>
    </div>

</x-app-layout>