@props(['label', 'name', 'required' => false])

<div class="mb-4">
    <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">
        {{ $label }} @if($required) <span class="text-[#dc2626]">*</span> @endif
    </label>
    <input 
        {{ $attributes->merge(['class' => 'w-full px-3 py-1.5 border border-[#94a3b8] rounded bg-white font-roboto font-medium text-base text-[#1f2937] focus:outline-none focus:border-[#33cc33] focus:ring-1 focus:ring-[#33cc33]' . ($errors->has($name) ? ' !border-red-500 !rounded-b-none' : '')]) }}
    />
    @error($name) 
        <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
            {{ $message }}
        </div>
    @enderror
</div>