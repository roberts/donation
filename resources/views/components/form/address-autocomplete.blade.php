<div x-data="{
    init() {
        if (typeof google === 'undefined') return;
        
        const autocomplete = new google.maps.places.Autocomplete(this.$refs.input, {
            types: ['address'],
            componentRestrictions: { country: 'us' },
            fields: ['address_components', 'geometry', 'icon', 'name']
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            let address1 = '';
            let postcode = '';
            let city = '';
            let state = '';

            for (const component of place.address_components) {
                const componentType = component.types[0];

                switch (componentType) {
                    case 'street_number': {
                        address1 = `${component.long_name} ${address1}`;
                        break;
                    }
                    case 'route': {
                        address1 += component.short_name;
                        break;
                    }
                    case 'postal_code': {
                        postcode = `${component.long_name}${postcode}`;
                        break;
                    }
                    case 'postal_code_suffix': {
                        postcode = `${postcode}-${component.long_name}`;
                        break;
                    }
                    case 'locality':
                        city = component.long_name;
                        break;
                    case 'administrative_area_level_1': {
                        state = component.short_name;
                        break;
                    }
                }
            }

            this.$wire.set('{{ $attributes->get('wire:model.blur') ?? $attributes->get('wire:model') }}', address1);
            
            // Try to set other fields if they exist in the form
            // This assumes standard naming conventions or passed in attributes
            @if($attributes->has('city-model'))
                this.$wire.set('{{ $attributes->get('city-model') }}', city);
            @endif
            
            @if($attributes->has('state-model'))
                this.$wire.set('{{ $attributes->get('state-model') }}', state);
            @endif
            
            @if($attributes->has('zip-model'))
                this.$wire.set('{{ $attributes->get('zip-model') }}', postcode);
            @endif
        });
    }
}">
    <x-form.input {{ $attributes }} x-ref="input" />
</div>