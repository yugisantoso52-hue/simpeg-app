@props([
    'action' => '',
    'searchName' => 'search',
    'searchValue' => request('search'),
    'searchPlaceholder' => 'Cari data...',

    'filterName' => null,
    'filterValue' => request('status'),
    'filterOptions' => [],

    'showReset' => true,
])

<form
    action="{{ $action }}"
    method="GET"
    class="mb-6">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

        {{-- Search --}}

        <div class="md:col-span-6">

            <input
                type="text"
                name="{{ $searchName }}"
                value="{{ $searchValue }}"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

        </div>

        {{-- Filter --}}

        @if($filterName)

            <div class="md:col-span-3">

                <select
                    name="{{ $filterName }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                    <option value="">Semua Data</option>

                    @foreach($filterOptions as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected($filterValue == $value)>

                            {{ $label }}

                        </option>

                    @endforeach

                </select>

            </div>

        @endif

        {{-- Tombol --}}

        <div class="md:col-span-3 flex gap-2">

            <button
                type="submit"
                class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold hover:bg-blue-700 transition">

                Cari

            </button>

            @if($showReset)

                <a
                    href="{{ $action }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-100 transition">

                    Reset

                </a>

            @endif

        </div>

    </div>

</form>