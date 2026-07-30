<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use Livewire\Component;

class LayoutLogout extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/login', navigate: false);
    }

    public function render()
    {
        return <<<'HTML'
        <button wire:click="logout" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700">
            Odhlásiť sa
        </button>
        HTML;
    }
}
