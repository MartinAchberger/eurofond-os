<?php

namespace App\Livewire\Pages;

use App\Models\Document;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Dokumenty — EUROFOND OS')]
class DocumentsIndex extends Component
{
    #[Computed]
    public function documents()
    {
        return Document::with(['project', 'type', 'versions' => fn ($q) => $q->where('status', \App\Enums\DocumentVersionStatus::Aktualna)])
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.documents-index');
    }
}
