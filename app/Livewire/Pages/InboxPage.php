<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Inbox — EUROFUND OS')]
class InboxPage extends Component
{
    public function render()
    {
        return view('livewire.pages.inbox-page');
    }
}
