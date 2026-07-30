<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Dokumenty — EUROFUND OS')]
class DocumentsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.documents-index');
    }
}
