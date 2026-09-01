@props([
    'create' => null,
    'import' => null,
    'exportExcel' => null,
    'exportPdf' => null,
    'searchAction' => null,
    'searchValue' => '',
    'placeholder' => 'Cari data...',
    'createLabel' => 'Tambah Data',
])

<div
    class="mb-6 flex flex-col gap-4
           lg:flex-row
           lg:items-center
           lg:justify-between">

    {{-- LEFT BUTTON --}}
    <div class="flex flex-wrap gap-2">

        @if($create)

        <a
            href="{{ $create }}"
            class="inline-flex items-center
                   rounded-lg
                   bg-blue-600
                   px-4 py-2
                   text-sm font-semibold
                   text-white
                   hover:bg-blue-700">

            <svg class="w-5 h-5 mr-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"/>

            </svg>

            {{ $createLabel }}

        </a>

        @endif


        @if($import)

        <a
            href="{{ $import }}"
            class="inline-flex items-center
                   rounded-lg
                   bg-emerald-600
                   px-4 py-2
                   text-sm
                   font-semibold
                   text-white
                   hover:bg-emerald-700">

            📥 Import Excel

        </a>

        @endif


        @if($exportExcel)

        <a
            href="{{ $exportExcel }}"
            class="inline-flex items-center
                   rounded-lg
                   bg-green-600
                   px-4 py-2
                   text-sm
                   font-semibold
                   text-white
                   hover:bg-green-700">

            📊 Export Excel

        </a>

        @endif


        @if($exportPdf)

        <a
            href="{{ $exportPdf }}"
            class="inline-flex items-center
                   rounded-lg
                   bg-red-600
                   px-4 py-2
                   text-sm
                   font-semibold
                   text-white
                   hover:bg-red-700">

            📄 Export PDF

        </a>

        @endif

    </div>


    {{-- SEARCH --}}

    @if($searchAction)

    <form
        action="{{ $searchAction }}"
        method="GET"
        class="flex gap-2">

        @if(request('filter'))
            <input type="hidden" name="filter" value="{{ request('filter') }}">
        @endif

        <input
            type="text"
            name="search"
            value="{{ $searchValue }}"
            placeholder="{{ $placeholder }}"
            class="w-72 rounded-lg border-gray-300 shadow-sm">

        <button
            class="rounded-lg
                   bg-gray-700
                   px-4
                   text-white
                   hover:bg-gray-800">

            Cari

        </button>

    </form>

    @endif

</div>