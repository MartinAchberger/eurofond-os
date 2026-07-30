<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Projekty — EUROFUND OS')]
class ProjectsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.projects-index');
    }
}
