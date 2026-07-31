<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class AuditHistory extends Component
{
    #[Computed]
    public function activities()
    {
        return Activity::with(['causer', 'subject'])->latest()->limit(6)->get();
    }

    public function render()
    {
        return view('livewire.dashboard.audit-history');
    }
}
