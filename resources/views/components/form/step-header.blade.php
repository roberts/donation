@props(['title', 'isValid' => false, 'collapsed' => false])

<div 
    class="p-4 text-white text-2xl flex justify-between items-center transition-colors duration-500 ease-in-out {{ $isValid ? 'bg-[#33cc33]' : 'bg-[#286090]' }} {{ $collapsed ? 'rounded-md' : 'rounded-t-md' }}"
>
    <span class="font-roboto">{{ $title }}</span>
    @if($isValid)
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 512 512" fill="currentColor">
            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
        </svg>
    @endif
</div>