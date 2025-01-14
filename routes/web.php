<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PjsipController;

Route::get('/', function () {
    return view('welcome');
});

// 各ページごとにルートを定義
Route::get('/pjsip', [PjsipController::class, 'index'])->name('pjsip.index');
Route::get('/pjsip/create', [PjsipController::class, 'create'])->name('pjsip.create');
Route::post('/pjsip', [PjsipController::class, 'store'])->name('pjsip.store');
Route::get('/pjsip/{id}/edit', [PjsipController::class, 'edit'])->name('pjsip.edit');
Route::put('/pjsip/{id}', [PjsipController::class, 'update'])->name('pjsip.update');
Route::delete('/pjsip/{id}', [PjsipController::class, 'destroy'])->name('pjsip.destroy');
Route::get('/pjsip/test', [PjsipController::class, 'test'])->name('pjsip.test');