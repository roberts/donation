<div>
    <x-form.step-header 
        title="Donation (3 of 4)" 
        :is-valid="$this->isStep3Valid()" 
        :collapsed="!$this->isStep2Valid()"
    />

    <div class="p-4 border-x border-b border-gray-200 rounded-b-md" x-show="$wire.step2Valid" style="display: none;">
        <p class="mb-4 text-slate-700 font-roboto">Please enter the total amount you would like to donate or click the <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-gray-400" viewBox="0 0 512 512" fill="currentColor"><path d="M96 0C60.7 0 32 28.7 32 64V448c0 35.3 28.7 64 64 64H352c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64H96zM208 288h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H208c-17.7 0-32-14.3-32-32s14.3-32 32-32zm96-128c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32s14.3-32 32-32H304zM176 384c17.7 0 32 14.3 32 32s-14.3 32-32 32H112c-17.7 0-32-14.3-32-32s14.3-32 32-32h64zm96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32H240c-17.7 0-32-14.3-32-32s14.3-32 32-32h32zM96 288c17.7 0 32 14.3 32 32s-14.3 32-32 32H64c-17.7 0-32-14.3-32-32s14.3-32 32-32H96z"/></svg> to use the maximum tax credit available for the year selected based on your filing status.</p>

        <!-- Donation Amount -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block font-roboto font-medium text-slate-700 text-sm mb-1">Donation Amount <span class="text-red-600">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                    <input type="number" step="0.01" wire:model.live="form.totalAmount" class="w-full px-3 py-1.5 border border-slate-400 rounded bg-white font-roboto text-base text-gray-800 focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 pl-8 pr-10 @error('form.totalAmount') !border-red-500 !rounded-b-none @enderror" placeholder="Enter amount or auto calculate max">
                    <button type="button" wire:click="useMaxDonation" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-[#33cc33]" title="Calculate Max Credit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 512 512" fill="currentColor"><!-- FontAwesome Calculator -->
                            <path d="M96 0C60.7 0 32 28.7 32 64V448c0 35.3 28.7 64 64 64H352c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64H96zM208 288h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H208c-17.7 0-32-14.3-32-32s14.3-32 32-32zm96-128c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32s14.3-32 32-32H304zM176 384c17.7 0 32 14.3 32 32s-14.3 32-32 32H112c-17.7 0-32-14.3-32-32s14.3-32 32-32h64zm96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32H240c-17.7 0-32-14.3-32-32s14.3-32 32-32h32zM96 288c17.7 0 32 14.3 32 32s-14.3 32-32 32H64c-17.7 0-32-14.3-32-32s14.3-32 32-32H96z"/>
                        </svg>
                    </button>
                </div>
                @error('form.totalAmount') 
                    <div class="bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="flex items-center">
                    @if($this->maxCredit > 0)
                    <span class="text-sm text-gray-600 font-roboto mt-6">
                        Max Credit for {{ $form->filingYear }}: <strong>${{ number_format($this->maxCredit, 2) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <!-- School Selection -->
        @if($form->totalAmount > 0)
            <div class="mb-4">
                <label class="block font-roboto font-medium text-slate-700 text-sm mb-1">Would you like to recommend a school?</label>
                
                <div class="flex gap-4 mb-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" wire:model.live="schoolRecommend" name="schoolRecommend" value="1" class="form-radio h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300">
                        <span class="ml-2 text-sm font-roboto text-gray-700">Yes</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" wire:model.live="schoolRecommend" name="schoolRecommend" value="0" class="form-radio h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300">
                        <span class="ml-2 text-sm font-roboto text-gray-700">No</span>
                    </label>
                </div>

                @if($schoolRecommend)
                    <div class="mb-4">
                        <label class="block font-roboto font-medium text-slate-700 text-sm mb-1">School type?</label>
                        <div class="flex gap-4 mb-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="schoolType" name="schoolType" value="public" class="form-radio h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300">
                                <span class="ml-2 text-sm font-roboto text-gray-700">Public / Charter</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="schoolType" name="schoolType" value="private" class="form-radio h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300">
                                <span class="ml-2 text-sm font-roboto text-gray-700">Private</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="schoolType" name="schoolType" value="other" class="form-radio h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300">
                                <span class="ml-2 text-sm font-roboto text-gray-700">Other</span>
                            </label>
                        </div>
                    </div>

                    @if($schoolType === 'other')
                        <input 
                            type="text" 
                            wire:model="form.customSchool" 
                            class="w-full px-3 py-1.5 border-b border-slate-300 bg-transparent font-roboto text-base text-gray-800 focus:outline-none focus:border-[#33cc33] placeholder-slate-400" 
                            placeholder="Enter school name..."
                        >
                    @elseif($schoolType)
                        <div x-data="{ 
                            query: '', 
                            results: [],
                            async search() {
                                this.results = await $wire.searchSchools(this.query);
                            },
                            select(id, name) {
                                $wire.selectSchool(id);
                                this.query = name;
                                this.results = [];
                            }
                        }" @click.outside="results = []">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    x-model="query" 
                                    @input.debounce.300ms="search"
                                    @focus="search"
                                    class="w-full px-3 py-1.5 border-b border-slate-300 bg-transparent font-roboto text-base text-gray-800 focus:outline-none focus:border-[#33cc33] placeholder-slate-400 @error('form.schoolId') !border-red-500 @enderror" 
                                    placeholder="Choose your school..."
                                >
                                
                                <div x-show="results.length > 0" class="absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 max-h-60 overflow-y-auto shadow-lg">
                                    <template x-for="school in results" :key="school.id">
                                        <div 
                                            @click="select(school.id, school.name)"
                                            class="p-2 hover:bg-blue-50 cursor-pointer text-sm"
                                            x-text="school.name"
                                        ></div>
                                    </template>
                                </div>
                            </div>

                            @if($selectedSchool)
                                <div class="mt-2 p-3 bg-lime-50 border border-lime-200 rounded text-lime-800 text-sm flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Selected: {{ $selectedSchool->name }} ({{ $selectedSchool->type }})
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>