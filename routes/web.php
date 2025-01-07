<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PjsipController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('pjsip', PjsipController::class);
Route::get('/pjsip/{id}/edit', [PjsipController::class, 'edit'])->name('pjsip.edit');
Route::put('/pjsip/{id}', [PjsipController::class, 'update'])->name('pjsip.update');
Route::delete('/pjsip/{id}', [PjsipController::class, 'destroy'])->name('pjsip.destroy');
Route::get('/pjsip/{id}/call', [PjsipController::class, 'call'])->name('pjsip.call');