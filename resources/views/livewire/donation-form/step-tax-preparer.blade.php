<div>
    <x-form.step-header 
        title="Tax Preparer Permission (Optional)" 
        :is-valid="false" 
    />

    <div class="p-4 border-x border-b border-gray-200 rounded-b-md">
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model.live="form.taxProfessionalEnable" class="mr-2 text-blue-600 focus:ring-blue-500">
                <span class="font-roboto font-medium text-[#334155] text-sm">Permission given for Tax Preparer contact.</span>
            </label>
            <p class="text-xs text-gray-500 ml-6">This allows IBE to share the details of your donation with your listed tax preparer.</p>
        </div>

        @if($form->taxProfessionalEnable)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <div class="md:col-span-1">
                    <x-form.input label="First Name" name="form.taxProfessionalFirstName" wire:model.blur="form.taxProfessionalFirstName" class="!mb-0" />
                </div>
                <div class="md:col-span-1">
                    <x-form.input label="Last Name" name="form.taxProfessionalLastName" wire:model.blur="form.taxProfessionalLastName" class="!mb-0" />
                </div>
                <div class="md:col-span-1">
                    <div class="w-full">
                        <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">Phone</label>
                        <input type="tel" x-mask="(999) 999-9999" wire:model.blur="form.taxProfessionalPhone" class="w-full px-3 py-1.5 border border-[#94a3b8] rounded bg-white font-roboto font-medium text-base text-[#1f2937] focus:outline-none focus:border-[#33cc33] focus:ring-2 focus:ring-[#33cc33]/20">
                        @error('form.taxProfessionalPhone') <span class="text-[#dc2626] text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="md:col-span-1">
                    <x-form.input label="Email" name="form.taxProfessionalEmail" wire:model.blur="form.taxProfessionalEmail" class="!mb-0" />
                </div>
            </div>
        @endif


    </div>
</div>