<?php

namespace App\Http\Controllers;

use App\Models\Chatting;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Menampilkan halaman chatting
    public function index()
    {
        return view('chat');
    }

    // Mengambil riwayat pesan
    public function messages()
    {
        $messages = Chatting::latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    // Mengirim pesan baru
    public function send(Request $request)
    {
        // Validasi pesan
        $validated = $request->validate([
            'sender' => 'required|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        // Simpan pesan ke database
        $chatting = Chatting::create([
            'sender' => $validated['sender'],
            'message' => $validated['message'],
        ]);

        // Memicu event broadcast
        MessageSent::dispatch($chatting);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim.',
            'data' => $chatting,
        ], 201);
    }
    // Menghapus seluruh percakapan
    public function deleteAll()
    {
        Chatting::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seluruh percakapan berhasil dihapus.'
        ]);
    }
}
