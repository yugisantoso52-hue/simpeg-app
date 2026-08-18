@if ($paginator->hasPages())

<nav class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-4">

    <div class="text-sm text-slate-600">

        Menampilkan

        <span class="font-semibold">

            {{ $paginator->firstItem() }}

        </span>

        -

        <span class="font-semibold">

            {{ $paginator->lastItem() }}

        </span>

        dari

        <span class="font-semibold">

            {{ $paginator->total() }}

        </span>

        data

    </div>

    <div class="flex items-center gap-2">

        {{-- Previous --}}

        @if ($paginator->onFirstPage())

            <span
                class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-slate-400">

                ←

            </span>

        @else

            <a

                href="{{ $paginator->previousPageUrl() }}"

                class="rounded-lg border border-slate-200 bg-white px-4 py-2 hover:bg-slate-50">

                ←

            </a>

        @endif

        {{-- Pages --}}

        @foreach ($elements as $element)

            @if(is_string($element))

                <span class="px-2">

                    ...

                </span>

            @endif

            @if(is_array($element))

                @foreach($element as $page=>$url)

                    @if($page==$paginator->currentPage())

                        <span
                            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">

                            {{ $page }}

                        </span>

                    @else

                        <a

                            href="{{ $url }}"

                            class="rounded-lg border border-slate-200 px-4 py-2 hover:bg-slate-50">

                            {{ $page }}

                        </a>

                    @endif

                @endforeach

            @endif

        @endforeach

        {{-- Next --}}

        @if($paginator->hasMorePages())

            <a

                href="{{ $paginator->nextPageUrl() }}"

                class="rounded-lg border border-slate-200 bg-white px-4 py-2 hover:bg-slate-50">

                →

            </a>

        @else

            <span
                class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-slate-400">

                →

            </span>

        @endif

    </div>

</nav>

@endif