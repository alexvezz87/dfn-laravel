<?php

use App\Http\Controllers\BookingPublicController;
use App\Http\Controllers\EventPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/evento/{event}', [EventPublicController::class, 'show'])->name('event.preview');
Route::post('/evento/{event}/prenota', [EventPublicController::class, 'book'])->name('event.book');
Route::post('/api/check-fai-card', [EventPublicController::class, 'checkFaiCard'])->name('fai.check-card');
Route::get('/prenotazione/{code}', [BookingPublicController::class, 'show'])->name('booking.show');
