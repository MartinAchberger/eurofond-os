<?php

test('login page is in slovak', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Heslo')
        ->assertSee('Prihlásiť sa')
        ->assertSee('Zabudli ste heslo?');
});
