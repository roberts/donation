<div class="bg-white rounded-md shadow-md p-8 mx-auto my-16 max-sm:my-0 w-full max-w-[1080px]" x-data="donationStripe(@entangle('step3Valid'))" @step-1-completed.window="$nextTick(() => document.getElementById('step-2-content').scrollIntoView({ behavior: 'smooth', block: 'center' }))">
    <form wire:submit.prevent="submit" class="space-y-8">
        <!-- Step 1: Your Information -->
        @include('livewire.donation-form.step-donor-info')

        <!-- Step 2: Tax Year -->
        @include('livewire.donation-form.step-tax-year')

        <!-- Step 3: Donation -->
        @include('livewire.donation-form.step-donation-amount')

        <!-- Step 4: Payment -->
        @include('livewire.donation-form.step-payment')

        <!-- Tax Preparer (Optional) -->
        @include('livewire.donation-form.step-tax-preparer')

        <div>
            <x-honeypot />

            @if ($errors->any())
                <div class="w-full md:w-1/3 mx-auto bg-red-50 border border-red-200 text-red-600 text-xs p-2 rounded relative mb-4 text-center flex items-center justify-between">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 384 512" fill="currentColor"><!-- FontAwesome Arrow Up -->
                        <path d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"/>
                    </svg>
                    @if($errors->has('payment'))
                        <span class="font-roboto">{{ $errors->first('payment') }}</span>
                    @else
                        <span class="font-roboto">Missing Information</span>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 384 512" fill="currentColor"><!-- FontAwesome Arrow Up -->
                        <path d="M214.6 41.4c-12.5-12.5-32.8-12.5-45.3 0l-160 160c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 141.2V448c0 17.7 14.3 32 32 32s32-14.3 32-32V141.2L329.4 246.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z"/>
                    </svg>
                </div>
            @endif

            <button 
                type="button" 
                id="submit-button"
                @click="submitPayment"
                :disabled="isProcessing"
                class="w-full md:w-1/3 block mx-auto bg-lime-500 hover:bg-lime-600 text-white font-bold font-roboto py-3 rounded transition cursor-pointer"
                :class="{ 'opacity-50 cursor-not-allowed': isProcessing }"
            >
                <span x-show="!isProcessing">Submit Donation</span>
                <span x-show="isProcessing">Processing...</span>
            </button>
            
            <div class="mt-4 text-center text-xs text-gray-500">
                <p>By clicking Submit Donation, you agree to our Terms of Service and Privacy Policy.</p>
            </div>
        </div>
    </form>

    @include('livewire.donation-form.stripe-script')
</div>
