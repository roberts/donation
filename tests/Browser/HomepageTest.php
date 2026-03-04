<?php

describe('Homepage', function () {
    it('has a homepage', function () {
        $page = $this->visit('/');

        $page->assertSee('IBE Foundation');
    });
});
