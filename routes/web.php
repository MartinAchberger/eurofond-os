<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\DecisionsIndex;
use App\Livewire\Pages\Dnes;
use App\Livewire\Pages\DocumentsIndex;
use App\Livewire\Pages\InboxPage;
use App\Livewire\Pages\ProfilePage;
use App\Livewire\Pages\ProjectShow;
use App\Livewire\Pages\ProjectsIndex;
use App\Livewire\Pages\QuestionsIndex;
use App\Livewire\Pages\RisksIndex;
use App\Livewire\Pages\SettingsPage;
use App\Livewire\Pages\TasksIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/dnes', Dnes::class)->name('dnes');
    Route::get('/inbox', InboxPage::class)->name('inbox');
    Route::get('/projekty', ProjectsIndex::class)->name('projekty.index');
    Route::get('/projekty/{project}', ProjectShow::class)->name('projekty.show');
    Route::get('/dokumenty', DocumentsIndex::class)->name('dokumenty.index');
    Route::get('/poziadavky', QuestionsIndex::class)->name('poziadavky.index');
    Route::get('/ulohy', TasksIndex::class)->name('ulohy.index');
    Route::get('/rizika', RisksIndex::class)->name('rizika.index');
    Route::get('/rozhodnutia', DecisionsIndex::class)->name('rozhodnutia.index');
    Route::get('/nastavenia', SettingsPage::class)->name('nastavenia');
    Route::get('/profile', ProfilePage::class)->name('profile');
});

require __DIR__.'/auth.php';
