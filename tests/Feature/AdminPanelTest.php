<?php

test('guest visiting the admin panel is redirected to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});
