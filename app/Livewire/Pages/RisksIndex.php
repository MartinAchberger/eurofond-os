<?php

namespace App\Livewire\Pages;

use App\Models\Risk;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Riziká — EUROFOND OS')]
class RisksIndex extends Component
{
    #[Computed]
    public function risks()
    {
        return Risk::with('project')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.risks-index');
    }
}
