<script>
    function donationStripe(step3Valid) {
        return {
            stripe: null,
            elements: null,
            card: null,
            step2Override: false,
            isProcessing: false,
            step3Valid: step3Valid,

            init() {
                const stripeKey = '{{ config('services.stripe.key') }}';
                if (!stripeKey) {
                    console.error('Stripe key is missing. Please set STRIPE_KEY in your .env file.');
                    return;
                }
                
                this.stripe = Stripe(stripeKey);
                this.elements = this.stripe.elements();
                
                const style = {
                    base: {
                        fontSize: '14px',
                        fontFamily: "'Roboto', Helvetica, Arial, Lucida, sans-serif",
                        color: '#1f2937',
                        '::placeholder': { color: '#9ca3af' }
                    },
                    invalid: {
                        color: '#dc2626',
                        iconColor: '#dc2626'
                    }
                };
                
                this.card = this.elements.create('card', { style });

                this.card.on('change', (event) => {
                    const displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                        displayError.classList.remove('hidden');
                    } else {
                        displayError.textContent = '';
                        displayError.classList.add('hidden');
                    }
                });
                
                this.$watch('step3Valid', (value) => {
                    if (value) {
                        setTimeout(() => {
                            this.card.unmount();
                            this.card.mount('#card-element');
                        }, 100);
                    }
                });

                this.$nextTick(() => {
                    if (this.step3Valid && document.getElementById('card-element')) {
                        this.card.mount('#card-element');
                    }
                });
            },

            async submitPayment() {
                if (this.isProcessing) return;
                this.isProcessing = true;

                try {
                    // 1. Validate on server first
                    await this.$wire.checkValidation();

                    // Access Livewire data
                    const donors = await this.$wire.get('form.donors');
                    const email = await this.$wire.get('form.email');
                    const phone = await this.$wire.get('form.phone');
                    
                    // Default to mailing address
                    let line1 = await this.$wire.get('form.address');
                    let city = await this.$wire.get('form.city');
                    let state = await this.$wire.get('form.state');
                    let postal_code = await this.$wire.get('form.zip');

                    // Check if billing address is different
                    const billingAddressEnable = await this.$wire.get('form.billingAddressEnable');
                    
                    if (!billingAddressEnable) {
                        line1 = await this.$wire.get('form.billingAddress');
                        city = await this.$wire.get('form.billingCity');
                        state = await this.$wire.get('form.billingState');
                        postal_code = await this.$wire.get('form.billingZip');
                    }

                    const { paymentMethod, error } = await this.stripe.createPaymentMethod({
                        type: 'card',
                        card: this.card,
                        billing_details: {
                            name: donors[0].first_name + ' ' + donors[0].last_name,
                            email: email,
                            phone: phone,
                            address: {
                                line1: line1,
                                city: city,
                                state: state,
                                postal_code: postal_code,
                                country: 'US',
                            }
                        }
                    });

                    if (error) {
                        const errorElement = document.getElementById('card-errors');
                        if (errorElement) {
                            errorElement.textContent = error.message;
                            errorElement.classList.remove('hidden');
                        }
                        this.isProcessing = false;
                    } else {
                        await this.$wire.submit(paymentMethod.id);
                        this.isProcessing = false;
                    }
                } catch (e) {
                    console.error(e);
                    this.isProcessing = false;
                }
            }
        }
    }
</script>