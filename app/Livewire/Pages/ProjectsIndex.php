<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Projekty — EUROFOND OS')]
class ProjectsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.projects-index');
    }
}
