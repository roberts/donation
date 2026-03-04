<div>
    <x-form.step-header 
        title="Tax Year (2 of 4)" 
        :is-valid="$this->isStep2Valid()" 
        :collapsed="!$this->isStep1Valid()"
    />

    <div id="step-2-content" class="p-4 border-x border-b border-gray-200 rounded-b-md" x-show="$wire.step1Valid || step2Override" style="display:none">
        <!-- Filing Year -->
        <div class="mb-4">
            <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">What tax year will you be claiming this Credit on your taxes? <span class="text-[#dc2626]">*</span></label>
            <p class="text-xs text-gray-500 mb-2">The tax year that you will claim your QCO donation. You have until April 15 each year to donate for the prior year.</p>
            <div class="flex gap-3">
                @foreach($this->availableYears as $year)
                    <label class="flex items-center">
                        <input type="radio" wire:model.live="form.filingYear" name="filingYear" value="{{ $year }}" class="form-radio mr-2 h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300"> {{ $year }}
                    </label>
                @endforeach
            </div>
            @error('form.filingYear') <span class="text-[#dc2626] text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- QCO Question -->
        <div class="mb-4">
            <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">Have you donated to another Arizona Qualified Charitable Organization for the {{ $form->filingYear ?? 'selected' }} tax year? <span class="text-[#dc2626]">*</span></label>
            <p class="text-xs text-gray-500 mb-2">This does not include any public school donations, private school STO donations, only donations to an Arizona QCO in an effort to claim a tax credit.</p>
            <div class="flex gap-3">
                <label class="flex items-center">
                    <input type="radio" wire:model.live="form.boolQCO" name="boolQCO" value="no" class="form-radio mr-2 h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300"> No
                </label>
                <label class="flex items-center">
                    <input type="radio" wire:model.live="form.boolQCO" name="boolQCO" value="yes" class="form-radio mr-2 h-5 w-5 text-[#33cc33] focus:ring-[#33cc33] border-gray-300"> Yes
                </label>
            </div>
            @error('form.boolQCO') <span class="text-[#dc2626] text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- QCO Details -->
        @if($form->boolQCO === 'yes')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <x-form.input label="QCO Name" name="form.qcoName" wire:model.blur="form.qcoName" required class="!mb-0" />
                </div>
                <div>
                    <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">Amount of QCO donation <span class="text-[#dc2626]">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" step="0.01" wire:model.live.debounce.500ms="form.qcoAmount" class="w-full px-3 py-1.5 border border-[#94a3b8] rounded bg-white font-roboto text-base text-[#1f2937] focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20 pl-8">
                    </div>
                    @error('form.qcoAmount') <span class="text-[#dc2626] text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            @if($form->qcoAmount > 0)
                <div class="bg-blue-50 p-4 rounded text-sm text-[#334155] mb-6">
                    The QCO donation amount of <strong>${{ number_format($form->qcoAmount, 2) }}</strong> will be automatically deducted from your calculated tax credit in step 3.
                </div>
            @endif
        @endif
    </div>
</div>