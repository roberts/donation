<?php

use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

describe('Donation Form', function () {
    it('can fill out the donation form', function () {
        $school = School::factory()->create(['name' => 'Browser Test School']);

        $browser = $this->visit('/donate')
            ->assertSee('IBE Foundation Donation')
            ->click('button[aria-labelledby="label-form.filingStatus"]')
            ->waitForText('Single');

        $browser->script("document.evaluate(\"//span[normalize-space(text())='Single']\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click();");

        $browser->type('input[wire\:model="form.donors.0.first_name"]', 'John')
            ->type('input[wire\:model="form.donors.0.last_name"]', 'Doe')
            ->type('input[wire\:model="form.phone"]', '555-555-5555')
            ->type('input[wire\:model="form.address"]', '123 Main St')
            ->type('input[wire\:model="form.city"]', 'Phoenix')
            ->click('button[aria-labelledby="label-form.state"]')
            ->waitForText('Arizona');

        $browser->script("document.evaluate(\"//span[normalize-space(text())='Arizona']\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click();");

        $browser->type('input[wire\:model="form.zip"]', '85001')
            ->type('input[wire\:model="form.email"]', 'john@example.com')
            ->type('input[wire\:model="form.email_confirmation"]', 'john@example.com')
            ->waitForText('Your Information (1 of 4)')
            ->click('input[wire\:model\.live="form.filingYear"][value="2025"]')
            ->click('input[wire\:model\.live="form.boolQCO"][value="no"]')
            ->type('input[wire\:model\.live="form.totalAmount"]', '100')
            ->click('input[wire\:model\.live="schoolRecommend"][value="1"]')
            ->waitForText('School type?')
            ->click('input[wire\:model\.live="schoolType"][value="public"]')
            ->type('input[x-model="query"]', 'Browser Test')
            ->waitForText('Browser Test School')
            ->click('.absolute.z-10 .cursor-pointer')
            ->waitForText('Selected: Browser Test School')
            ->assertVisible('#card-element')
            ->assertSee('My credit card address is the same as above.');
    });
});
