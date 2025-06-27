@php
    $id = $getId();
    $statePath = $getStatePath();
    $options = $getOptions();
    $state = $getState() ?? array_key_first($options);
    $duskSelector = $getDuskSelector();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$statePath"
>
    <div
        x-data="{ open: false }"
        @click.away="open = false"
        @keydown.escape.window="open = false"
        class="relative"
    >
        <button
            type="button"
            @click="open = !open"
            @if($duskSelector) data-dusk="{{ $duskSelector }}-button" @endif
            class="relative w-full cursor-pointer rounded-lg bg-white dark:bg-gray-800 dark:ring-gray-700 py-2 pl-3 pr-10 text-left shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-600 sm:text-sm sm:leading-6"
            aria-haspopup="listbox"
            :aria-expanded="open"
        >
            <div class="flex justify-between items-center px-2">
                <span class="block truncate font-bold">
                    {{ $options[$state]['title'] ?? 'Select an option' }}
                </span>
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                </svg>
            </div>
        </button>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-10 mt-1 w-full"
        >
            <ul
                class="max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-800 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                tabindex="-1"
                role="listbox"
            >
                @foreach ($options as $value => $option)
                    <li
                        wire:click="$set('{{ $statePath }}', '{{ $value }}')"
                        @click="open = false"
                        @if($duskSelector) data-dusk="{{ $duskSelector }}-{{ $value }}-card" @endif
                        class="relative cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-700 {{ $state === $value ? 'bg-primary-50 dark:bg-primary-900' : '' }}"
                        role="option"
                    >
                        <div class="w-full p-2">
                            <div class="flex items-center justify-between w-full">
                                <div class="space-y-0.5">
                                    <p class="text-sm font-medium text-gray-900 {{ $state === $value ? 'font-bold' : '' }}">
                                        {{ $option['title'] }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $option['description'] }}
                                    </p>
                                </div>
                                @if($state === $value)
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="h-4 w-4 text-primary-600 shrink-0 ml-3"
                                    >
                                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-dynamic-component>
