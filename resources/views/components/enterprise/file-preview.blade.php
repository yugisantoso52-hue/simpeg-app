@props([

    'file' => null,

    'label' => 'Dokumen',

    'emptyText' => 'Belum ada file.',

])

<div class="rounded-xl border border-gray-200 bg-white p-4">

    <label class="mb-2 block text-sm font-semibold text-gray-700">

        {{ $label }}

    </label>

    @if($file)

        <div class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3">

            <div class="flex items-center gap-3">

                <div class="rounded-lg bg-green-100 p-2">

                    📄

                </div>

                <div>

                    <p class="font-medium text-gray-800">

                        {{ basename($file) }}

                    </p>

                    <p class="text-sm text-gray-500">

                        File tersedia

                    </p>

                </div>

            </div>

            <div class="flex gap-2">

                <a
                    href="{{ route('document.preview', ['path' => $file]) }}"
                    target="_blank"
                    class="rounded-lg bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">

                    Lihat

                </a>

                <a
                    href="{{ route('document.preview', ['path' => $file]) }}"
                    download
                    class="rounded-lg bg-gray-700 px-3 py-2 text-white hover:bg-gray-800">

                    Download

                </a>

            </div>

        </div>

    @else

        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 py-8 text-center text-gray-500">

            {{ $emptyText }}

        </div>

    @endif

</div>