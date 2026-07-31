<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Profil — EUROFOND OS')]
class ProfilePage extends Component
{
    public function render()
    {
        return view('livewire.pages.profile-page');
    }
}
