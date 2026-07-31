<?php

namespace App\Livewire\Pages;

use App\Models\Decision;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Rozhodnutia — EUROFOND OS')]
class DecisionsIndex extends Component
{
    #[Computed]
    public function decisions()
    {
        return Decision::with('project')
            ->latest('approved_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.decisions-index');
    }
}
