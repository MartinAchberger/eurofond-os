<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Dnes — EUROFOND OS')]
class Dnes extends Component
{
    public function render()
    {
        return view('livewire.pages.dnes');
    }
}
