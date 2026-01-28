<?php

it('returns a successful response', function () {
    $this->markTestSkipped('No root route defined in package');

    $response = $this->get('/');

    $response->assertStatus(200);
});
