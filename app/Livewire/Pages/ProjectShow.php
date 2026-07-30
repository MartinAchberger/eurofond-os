<?php

namespace App\Livewire\Pages;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Projekt — EUROFUND OS')]
class ProjectShow extends Component
{
    public Project $project;

    #[Url]
    public string $tab = 'prehlad';

    public function render()
    {
        return view('livewire.pages.project-show');
    }
}
