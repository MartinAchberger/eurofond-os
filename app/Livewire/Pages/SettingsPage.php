<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Nastavenia — EUROFUND OS')]
class SettingsPage extends Component
{
    public function render()
    {
        return view('livewire.pages.settings-page');
    }
}
