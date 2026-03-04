<div>
    <x-form.step-header 
        title="Your Information (1 of 4)" 
        :is-valid="$this->isStep1Valid()" 
    />

    <div class="p-4 border-x border-b border-gray-200 rounded-b-md">
        <!-- Filing Status -->
        <div class="w-full md:w-1/3">
            <x-form.select label="Filing Status" name="form.filingStatus" wire:model.live="form.filingStatus" wire:blur="validateField('form.filingStatus')" required>
                @foreach(\App\Enums\FilingStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-form.select>
        </div>

        <!-- Donors Repeater -->
        <div class="mb-4">
            <label class="block font-roboto font-bold text-slate-700 text-sm mb-1">Donors</label>
            
            <div class="border border-gray-200 rounded-md p-4">
                <!-- Headers -->
                <div class="hidden md:flex gap-3 mb-2">
                    <div class="w-24">
                        <label class="block text-xs font-medium text-slate-500 font-roboto">Title</label>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 font-roboto">First Name <span class="text-red-600">*</span></label>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 font-roboto">Last Name <span class="text-red-600">*</span></label>
                    </div>
                    <div class="w-10"></div>
                </div>

                @foreach($form->donors as $index => $donor)
                    <div class="flex flex-col md:flex-row gap-3 mb-4 last:mb-0 md:items-stretch">
                        <div class="w-full md:w-24">
                            <label class="md:hidden block text-xs font-medium text-slate-500 mb-1 font-roboto">Title</label>
                            <select wire:model.blur="form.donors.{{ $index }}.title" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-1 focus:ring-[#33cc33]">
                                <option value="">Select...</option>
                                @foreach(\App\Enums\DonorTitle::cases() as $title)
                                    <option value="{{ $title->value }}">{{ $title->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="md:hidden block text-xs font-medium text-slate-500 mb-1 font-roboto">First Name <span class="text-red-600">*</span></label>
                            <input type="text" wire:model="form.donors.{{ $index }}.first_name" wire:blur="validateDonorField({{ $index }}, 'first_name')" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 @error("form.donors.{$index}.first_name") !border-red-500 !rounded-b-none @enderror">
                            @error("form.donors.{$index}.first_name") 
                                <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="w-full md:flex-1">
                            <label class="md:hidden block text-xs font-medium text-slate-500 mb-1 font-roboto">Last Name <span class="text-red-600">*</span></label>
                            <input type="text" wire:model="form.donors.{{ $index }}.last_name" wire:blur="validateDonorField({{ $index }}, 'last_name')" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 @error("form.donors.{$index}.last_name") !border-red-500 !rounded-b-none @enderror">
                            @error("form.donors.{$index}.last_name") 
                                <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Controls -->
                        <div class="flex flex-col justify-center w-10 items-center">
                            <!-- Trash Can -->
                            @if($index > 0)
                                <button type="button" wire:click="removeDonor({{ $index }})" class="text-gray-400 hover:text-red-500" title="Remove Donor">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 448 512" fill="currentColor"><!-- FontAwesome Trash Can Regular -->
                                        <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z"/>
                                    </svg>
                                </button>
                            @else
                                <button type="button" class="text-gray-300 cursor-not-allowed" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 448 512" fill="currentColor"><!-- FontAwesome Trash Can Regular -->
                                        <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($form->filingStatus === \App\Enums\FilingStatus::MarriedFilingJointly->value && count($form->donors) < 2)
                    <button type="button" wire:click="addDonor" class="appearance-none font-bold rounded outline-none flex !text-xs px-7 py-3 items-center !mb-0 ring-offset-2 ring-lime-400 focus-visible:ring-2 shadow border border-lime-500 text-lime-500 bg-lime-50 hover:bg-lime-100 transition-all">
                        + Secondary Donor
                    </button>
                @endif
            </div>
        </div>

        <!-- Phone -->
        <div class="mb-4">
            <div class="w-full md:w-1/3">
                <label class="block font-roboto font-medium text-slate-700 text-sm mb-1">Phone <span class="text-red-600">*</span></label>
                <div class="flex">
                    <input type="tel" x-mask="(999) 999-9999" placeholder="(___) ___-____" wire:model="form.phone" wire:blur="validateField('form.phone')" class="w-full px-3 py-1.5 border border-slate-400 rounded-l bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 border-r-0 @error('form.phone') !border-red-500 !rounded-bl-none @enderror">
                    <span class="inline-flex items-center px-3 rounded-r border border-slate-400 bg-gray-100 text-gray-500 @error('form.phone') !border-red-500 !rounded-br-none @enderror">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor"><!-- FontAwesome Phone -->
                            <path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/>
                        </svg>
                    </span>
                </div>
                @error('form.phone') 
                    <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Address Section -->
        <div x-data="{ showAddressFields: {{ !empty($form->address) ? 'true' : 'false' }} }">
            <!-- Address -->
            <x-form.address-autocomplete 
                label="Address" 
                name="form.address" 
                wire:model="form.address" 
                wire:blur="validateField('form.address')"
                required 
                city-model="form.city"
                state-model="form.state"
                zip-model="form.zip"
                @focus="showAddressFields = true"
            />

            <!-- City/State/Zip -->
            <div class="mb-4" x-show="showAddressFields" x-transition style="display: none;">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="w-full md:flex-1">
                        <x-form.input label="City" name="form.city" wire:model="form.city" wire:blur="validateField('form.city')" required class="!mb-0" />
                    </div>
                    <div class="w-full md:w-32">
                        <x-form.select label="State" name="form.state" wire:model="form.state" wire:blur="validateField('form.state')" autocomplete="address-level1" required class="!mb-0">
                            @foreach(\App\Enums\State::cases() as $state)
                                <option value="{{ $state->value }}">{{ $state->label() }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div class="w-full md:w-32">
                        <x-form.input label="Zip Code" name="form.zip" wire:model="form.zip" wire:blur="validateField('form.zip')" x-mask="99999" required class="!mb-0" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div>
                <label for="email" class="block font-roboto font-medium text-slate-700 text-sm mb-1">Email Address <span class="text-red-600">*</span></label>
                <div class="relative">
                    <input type="email" id="email" name="email" autocomplete="email" wire:model="form.email" wire:blur="validateField('form.email')" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 pr-10 @error('form.email') !border-red-500 !rounded-b-none @enderror">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor"><!-- FontAwesome Envelope -->
                            <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                        </svg>
                    </div>
                </div>
                @error('form.email') 
                    <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div>
                <label for="email_confirmation" class="block font-roboto font-medium text-slate-700 text-sm mb-1">Confirm Email Address <span class="text-red-600">*</span></label>
                <div class="relative">
                    <input type="email" id="email_confirmation" name="email_confirmation" autocomplete="email" wire:model="form.email_confirmation" wire:blur="validateField('form.email_confirmation')" @focus="step2Override = true" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto font-medium text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 pr-10 @error('form.email_confirmation') !border-red-500 !rounded-b-none @enderror">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 512 512" fill="currentColor"><!-- FontAwesome Envelope -->
                            <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                        </svg>
                    </div>
                </div>
                @error('form.email_confirmation') 
                    <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="bg-blue-50 p-4 rounded text-sm text-slate-700 mb-4 font-roboto text-center">
            The opportunity to recommend a school will become available as soon as the amount of your donation is added below.
        </div>
    </div>
</div>