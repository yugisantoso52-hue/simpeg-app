@props([

    'title' => null,

    'subtitle' => null,

    'striped' => true,

    'hover' => true,

    'responsive' => true,

    'loading' => false,

    'pagination' => null,

    'total' => null,

])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- Header --}}
    @if($title || $subtitle)

        <div class="px-6 py-4 border-b border-gray-200">

            @if($title)
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $title }}
                </h3>
            @endif

            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif

        </div>

    @endif


    {{-- Toolbar --}}
    @isset($toolbar)

        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">

            {{ $toolbar }}

        </div>

    @endisset


    {{-- Responsive --}}
    @if($responsive)

        <div class="overflow-x-auto">

    @endif


    <table class="min-w-full divide-y divide-gray-200">

        {{-- Head --}}
        <thead class="bg-slate-100">

            {{ $head }}

        </thead>


        {{-- Body --}}
        <tbody
            class="
                bg-white
                @if($striped)
                    divide-y divide-gray-100
                @endif
            "
        >

            @if($loading)

                <tr>

                    <td colspan="100%" class="py-12 text-center text-gray-500">

                        Memuat data...

                    </td>

                </tr>

            @else

                {{ $slot }}

            @endif

        </tbody>

    </table>


    @if($responsive)

        </div>

    @endif


    {{-- Footer --}}
    @if($pagination || $total)

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50"
        >

            <div class="text-sm text-gray-500">

                @if($total)

                    {{ $total }}

                @endif

            </div>

            <div>

                {{ $pagination }}

            </div>

        </div>

    @endif

</div>