@php
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $radioGroupAttributes = $getRadioGroupExtraAttributes();
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
    <div {{
        \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(
            \Filament\Support\prepare_inherited_attributes(new \Illuminate\View\ComponentAttributeBag($radioGroupAttributes))
                ->merge(['class' => 'flex gap-4'])
                ->class(['opacity-70' => $isDisabled])
        )
    }}>
        @foreach ($getOptions() as $value => $label)
            <label class="flex-1 cursor-pointer" for="{{ $statePath }}-{{ $value }}">
                <input
                    type="radio"
                    name="{{ $statePath }}"
                    id="{{ $statePath }}-{{ $value }}"
                    value="{{ $value }}"
                    wire:model.live="{{ $statePath }}"
                    @if($duskSelector) data-dusk="{{ $duskSelector }}-{{ $value }}" @endif
                    class="sr-only"
                />

                <div @class([
                    'rounded-lg border-2 p-4',
                    'border-primary-500 bg-primary-50 dark:bg-primary-900 dark:border-primary-500' => $getState() === $value,
                    'border-gray-300 dark:border-gray-600' => $getState() !== $value,
                ]) @if($duskSelector) data-dusk="{{ $duskSelector }}-{{ $value }}-card" @endif>
                    <div class="text-sm">
                        <p class="font-medium text-gray-900">{{ $label }}</p>
                        @if (isset($getDescriptions()[$value]))
                            <p class="text-gray-500">{{ $getDescriptions()[$value] }}</p>
                        @endif
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</x-dynamic-component>
