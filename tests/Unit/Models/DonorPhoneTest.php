<?php

use App\Models\Donor;

describe('Donor Phone Model', function () {
    it('sanitizes us phone numbers', function () {
        $donor = new Donor;

        $donor->phone = '(123) 456-7890';
        expect($donor->phone)->toBe('1234567890');

        $donor->phone = '123-456-7890';
        expect($donor->phone)->toBe('1234567890');
    });

    it('removes leading one from us numbers', function () {
        $donor = new Donor;

        $donor->phone = '+1 (123) 456-7890';
        expect($donor->phone)->toBe('1234567890');

        $donor->phone = '1-123-456-7890';
        expect($donor->phone)->toBe('1234567890');

        $donor->phone = '11234567890';
        expect($donor->phone)->toBe('1234567890');
    });

    it('preserves foreign numbers', function () {
        $donor = new Donor;

        // UK number: +44 7911 123456 (12 digits)
        $donor->phone = '+44 7911 123456';
        expect($donor->phone)->toBe('447911123456');

        // Random 11 digit number not starting with 1
        $donor->phone = '22345678901';
        expect($donor->phone)->toBe('22345678901');
    });

    it('handles null and empty', function () {
        $donor = new Donor;

        $donor->phone = null;
        expect($donor->phone)->toBeNull();

        $donor->phone = '';
        expect($donor->phone)->toBeNull();
    });

    it('static formatter', function () {
        expect(Donor::formatPhone('(123) 456-7890'))->toBe('1234567890')
            ->and(Donor::formatPhone('+1 (123) 456-7890'))->toBe('1234567890')
            ->and(Donor::formatPhone('+44 7911 123456'))->toBe('447911123456')
            ->and(Donor::formatPhone(null))->toBeNull()
            ->and(Donor::formatPhone(''))->toBeNull();
    });
});
