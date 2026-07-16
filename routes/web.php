<?php

use App\Livewire\Storefront;
use Illuminate\Support\Facades\Route;

Route::get('/', Storefront::class)->name('storefront');