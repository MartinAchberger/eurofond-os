<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Riziká — EUROFOND OS')]
class RisksIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.risks-index');
    }
}
