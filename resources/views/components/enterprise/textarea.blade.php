@props([
    'label' => null,
    'name',
    'value' => '',
    'rows' => 4,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'hint' => null,
])

<div>

    @if($label)

        <label
            for="{{ $name }}"
            class="mb-2 block text-sm font-semibold text-gray-700">

            {{ $label }}

            @if($required)

                <span class="text-red-500">*</span>

            @endif

        </label>

    @endif

    <textarea

        id="{{ $name }}"

        name="{{ $name }}"

        rows="{{ $rows }}"

        placeholder="{{ $placeholder }}"

        @required($required)

        @disabled($disabled)

        @readonly($readonly)

        {{ $attributes->merge([

            'class' => '

                block
                w-full
                rounded-xl
                border-gray-300
                shadow-sm
                text-sm

                focus:border-blue-500
                focus:ring-blue-500

            '

        ]) }}

    >{{ old($name,$value) }}</textarea>

    @error($name)

        <p class="mt-1 text-sm text-red-600">

            {{ $message }}

        </p>

    @enderror

    @if($hint)

        <p class="mt-1 text-xs text-gray-500">

            {{ $hint }}

        </p>

    @endif

</div>