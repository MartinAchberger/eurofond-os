<?php

it('redirects root to the dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
