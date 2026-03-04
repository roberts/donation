@props(['label', 'name', 'required' => false, 'placeholder' => 'Select...'])

@php
    $wireModel = $attributes->wire('model');
    $propertyName = $wireModel->value();
@endphp

<div class="mb-4" 
    x-data="{
        open: false,
        value: @entangle($propertyName),
        options: [],
        init() {
            this.options = Array.from(this.$refs.select.options).map(option => ({
                value: option.value,
                label: option.text
            }));
        },
        get selectedLabel() {
            const option = this.options.find(o => o.value == this.value);
            return option ? option.label : '{{ $placeholder }}';
        },
        select(val) {
            this.value = val;
            this.open = false;
        }
    }"
    @click.outside="open = false"
>
    <label for="{{ $name }}" id="label-{{ $name }}" class="block font-roboto font-medium text-[#334155] text-sm mb-1">
        {{ $label }} @if($required) <span class="text-[#dc2626]">*</span> @endif
    </label>

    <div class="relative">
        <button 
            type="button" 
            @click="open = !open"
            {{ $attributes->except(['class', 'wire:model', 'wire:model.live', 'wire:model.blur', 'autocomplete', 'name', 'required']) }}
            class="w-full px-3 py-2.5 border border-[#94a3b8] rounded bg-white font-sans text-sm text-[#1f2937] text-left flex justify-between items-center focus:outline-none focus:border-[#33cc33] focus:ring-1 focus:ring-[#33cc33] {{ $errors->has($name) ? '!border-red-500 !rounded-b-none' : '' }}"
            aria-haspopup="listbox"
            :aria-expanded="open"
            aria-labelledby="label-{{ $name }}"
        >
            <span x-text="selectedLabel" :class="{'text-gray-500': !value}"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div 
            x-show="open" 
            x-transition
            class="absolute z-50 w-full bg-white border border-[#94a3b8] border-t-0 rounded-b shadow-lg max-h-60 overflow-y-auto"
            style="display: none;"
            @mousedown.prevent
            role="listbox"
            tabindex="-1"
        >
            <template x-for="option in options" :key="option.value">
                <div 
                    x-show="option.value !== ''"
                    @click="select(option.value)"
                    class="relative pl-8 pr-2 py-2.5 cursor-pointer text-sm font-sans select-none flex items-center"
                    :class="{
                        'bg-lime-50 text-lime-700': value == option.value,
                        'hover:bg-lime-50 hover:text-lime-700': value != option.value
                    }"
                    role="option"
                    :aria-selected="value == option.value"
                >
                    <span class="absolute left-2 flex items-center justify-center" x-show="value == option.value">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span x-text="option.label"></span>
                </div>
            </template>
        </div>
    </div>

    <select 
        x-ref="select" 
        x-model="value"
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->only(['autocomplete']) }}
        class="absolute inset-0 w-full h-full opacity-0 pointer-events-none -z-10"
        tabindex="-1"
        aria-hidden="true"
    >
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>

    @error($name) 
        <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
            {{ $message }}
        </div>
    @enderror
</div>