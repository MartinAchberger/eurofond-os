<?php

namespace App\Livewire\Pages;

use App\Models\InboxItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Inbox — EUROFOND OS')]
class InboxPage extends Component
{
    #[Computed]
    public function items()
    {
        return InboxItem::with('suggestedProject')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.inbox-page');
    }
}
