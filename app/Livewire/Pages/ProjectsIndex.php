<?php

namespace App\Livewire\Pages;

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Projekty — EUROFOND OS')]
class ProjectsIndex extends Component
{
    #[Url]
    public string $q = '';

    #[Computed]
    public function projects()
    {
        return Project::with('client')
            ->when($this->q !== '', function ($query) {
                $query->where(fn ($w) => $w
                    ->where('code', 'like', "%{$this->q}%")
                    ->orWhere('name', 'like', "%{$this->q}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->q}%")));
            })
            ->orderBy('code')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.projects-index');
    }
}
