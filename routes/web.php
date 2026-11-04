<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Halaman utama chatting
Route::get('/', [ChatController::class, 'index'])
    ->name('chat.index');

// Mengambil riwayat pesan
Route::get('/chat/messages', [ChatController::class, 'messages'])
    ->name('chat.messages');

// Mengirim pesan
Route::post('/chat/send', [ChatController::class, 'send'])
    ->name('chat.send');

Route::delete('/chat/delete-all', [ChatController::class, 'deleteAll'])
    ->name('chat.deleteAll');
