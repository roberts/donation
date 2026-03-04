<div>
    <x-form.step-header 
        title="Payment (4 of 4)" 
        :is-valid="false" 
        :collapsed="!$this->isStep3Valid()"
    />

    <div class="p-4 border-x border-b border-gray-200 rounded-b-md" x-show="step3Valid" style="display: none;">
        <!-- Stripe Element -->
        <div class="mb-4" wire:ignore>
            <label class="block font-roboto font-medium text-[#334155] text-sm mb-1">Credit Card <span class="text-[#dc2626]">*</span></label>
            <div id="card-element" class="rounded-lg border border-slate-300 bg-white p-4 shadow-sm"></div>
            <div id="card-errors" class="hidden bg-red-50 text-red-600 text-xs p-2 rounded-b border border-red-200 border-t-0"></div>
        </div>

        <!-- Billing Address Checkbox -->
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model.live="form.billingAddressEnable" class="mr-2 text-[#33cc33] focus:ring-[#33cc33]">
                <span class="font-roboto font-medium text-[#334155] text-sm">My credit card address is the same as above.</span>
            </label>
        </div>

        @if(!$form->billingAddressEnable)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4" x-data="{ showBillingAddressFields: {{ !empty($form->billingAddress) ? 'true' : 'false' }} }">
                <div class="md:col-span-4">
                    <x-form.address-autocomplete 
                        label="Address" 
                        name="form.billingAddress" 
                        wire:model.blur="form.billingAddress" 
                        required 
                        class="!mb-0"
                        city-model="form.billingCity"
                        state-model="form.billingState"
                        zip-model="form.billingZip"
                        @focus="showBillingAddressFields = true"
                    />
                </div>
                <div class="md:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-3" x-show="showBillingAddressFields" x-transition style="display: none;">
                    <div class="md:col-span-2">
                        <x-form.input label="City" name="form.billingCity" wire:model.blur="form.billingCity" required class="!mb-0" />
                    </div>
                    <div class="md:col-span-1">
                        <x-form.select label="State" name="form.billingState" wire:model.blur="form.billingState" required class="!mb-0">
                            @foreach(\App\Enums\State::cases() as $state)
                                <option value="{{ $state->value }}">{{ $state->label() }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div class="md:col-span-1">
                        <x-form.input label="Zip Code" name="form.billingZip" wire:model.blur="form.billingZip" x-mask="99999" required class="!mb-0" />
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>