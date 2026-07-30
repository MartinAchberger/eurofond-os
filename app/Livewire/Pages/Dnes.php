<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Dnes — EUROFUND OS')]
class Dnes extends Component
{
    public function render()
    {
        return view('livewire.pages.dnes');
    }
}
