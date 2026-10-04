<?php

declare(strict_types=1);

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::get('/packages', [BookingController::class, 'packages'])->name('packages.index');
Route::get('/slots', [BookingController::class, 'slots'])->name('slots.index');
Route::get('/bookings', [BookingController::class, 'bookings'])->name('bookings.index');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
