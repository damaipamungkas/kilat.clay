<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function handleChat(Request $request)
    {
        $userMessage = $request->input('message');
        $context = $request->input('context');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['reply' => 'Maaf, konfigurasi API Key belum diatur di server .env.'], 500);
        }

        $prompt = "Anda adalah asisten AI resmi untuk 'KILAT' (Kediri Inline Skate School). Jawablah pertanyaan pengguna berikut secara ramah, profesional, dan akurat berdasarkan informasi FAQ berikut:\n\n{$context}\n\nPertanyaan Pengguna: {$userMessage}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses jawaban saat ini.';
                return response()->json(['reply' => $botReply]);
            } else {
                return response()->json(['reply' => 'Maaf, gagal mendapatkan respons dari layanan AI.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Maaf, terjadi kesalahan koneksi server.'], 500);
        }
    }
}
