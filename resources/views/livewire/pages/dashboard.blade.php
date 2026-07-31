<div class="space-y-6">
    <livewire:dashboard.stat-cards />

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <livewire:dashboard.portfolio-table />
        </div>
        <div>
            <livewire:dashboard.today-priorities />
        </div>
    </div>

    <livewire:dashboard.audit-history />

    <livewire:tasks.create-task-modal />
</div>
