<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Úlohy — EUROFOND OS')]
class TasksIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.tasks-index');
    }
}
