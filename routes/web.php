<?php

use App\Http\Controllers\EventPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/evento/{event}', [EventPublicController::class, 'show'])->name('event.preview');
