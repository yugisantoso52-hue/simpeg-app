@props([

    'back'=>null,

    'submit'=>'Simpan',

    'draft'=>false,

])

<div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6 mt-8">

    @if($back)

        <a
            href="{{ $back }}"
            class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 font-medium text-slate-700 hover:bg-slate-100">

            Batal

        </a>

    @endif

    @if($draft)

        <button
            type="submit"
            name="draft"
            value="1"
            class="rounded-lg bg-amber-500 px-5 py-2.5 font-medium text-white hover:bg-amber-600">

            Simpan Draft

        </button>

    @endif

    <button
        type="submit"
        class="rounded-lg bg-blue-600 px-6 py-2.5 font-semibold text-white hover:bg-blue-700">

        {{ $submit }}

    </button>

</div>