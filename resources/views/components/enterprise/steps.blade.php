@props([
    'steps' => [],
    'current' => 1
])

<div class="w-full py-4 mb-6">
    <div class="flex items-center justify-between w-full">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $loop->iteration;
                $isCompleted = $stepNumber < $current;
                $isCurrent = $stepNumber == $current;
                
                // Pengecekan cerdas: jika berupa array, ambil 'label' atau 'name'. Jika bukan, langsung jadikan string.
                $stepLabel = is_array($step) ? ($step['label'] ?? $step['name'] ?? 'Step ' . $stepNumber) : $step;
            @endphp

            <div class="flex items-center relative {{ !$loop->last ? 'w-full' : '' }}">
                <div class="flex items-center z-10">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-200',
                        'bg-blue-600 text-white' => $isCurrent,
                        'bg-green-500 text-white' => $isCompleted,
                        'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => !$isCurrent && !$isCompleted,
                    ])>
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>
                    <span @class([
                        'ml-3 text-sm font-medium hidden sm:inline-block',
                        'text-blue-600 font-semibold' => $isCurrent,
                        'text-green-600' => $isCompleted,
                        'text-gray-500 dark:text-gray-400' => !$isCurrent && !$isCompleted,
                    ])>
                        {{ $stepLabel }}
                    </span>
                </div>

                @if(!$loop->last)
                    <div @class([
                        'flex-1 h-0.5 mx-4 transition-colors duration-200',
                        'bg-green-500' => $isCompleted,
                        'bg-gray-200 dark:bg-gray-700' => !$isCompleted,
                    ])></div>
                @endif
            </div>
        @endforeach
    </div>
</div>