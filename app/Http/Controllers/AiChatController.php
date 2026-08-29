<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    public function handleChat(Request $request)
    {
        $userMessage       = $request->input('message');
        $faqContext        = $request->input('faqContext', '');
        $customRules       = $request->input('customRules', 'Anda adalah asisten AI resmi untuk "KILAT". Jawablah dengan ramah.');
        $knowledgeBase     = $request->input('knowledgeBase', '');
        $customInstruction = $request->input('customInstruction', '');

        $apiKey = env('GEMINI_API_KEY') ?? env('GOOGLE_API_KEY');

        // Jika API Key kosong atau bermasalah, gunakan AI Simulator Lokal yang cerdas, sopan, dan ber-emoticon
        if (!$apiKey || str_starts_with($apiKey, 'AIzaSy...')) {
            return response()->json(['reply' => $this->getSmartAiPersonaResponse($userMessage, $knowledgeBase, $customRules)]);
        }

        $prompt = "ATURAN / PERSONA:\n{$customRules}\n\nBASIS PENGETAHUAN:\n{$knowledgeBase}\n\nFAQ:\n{$faqContext}\n\nPertanyaan: {$userMessage}";

        try {
            $response = Http::withoutVerifying()->timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($botReply) {
                    return response()->json(['reply' => $botReply]);
                }
            }

            return response()->json(['reply' => $this->getSmartAiPersonaResponse($userMessage, $knowledgeBase, $customRules)]);

        } catch (\Exception $e) {
            return response()->json(['reply' => $this->getSmartAiPersonaResponse($userMessage, $knowledgeBase, $customRules)]);
        }
    }

    // Fungsi Simulator AI Lokal yang Ramah, Sopan, dan Ber-emoticon
    private function getSmartAiPersonaResponse($message, $knowledgeBase, $customRules)
    {
        $msgLower = strtolower(trim($message));

        // 1. Tanggapan Sapaan Umum
        if (in_array($msgLower, ['halo', 'hai', 'hallo', 'hey', 'pagi', 'siang', 'sore', 'malam'])) {
            return "Halo juga! 😊 Selamat datang di Pusat Bantuan KILAT ⛸️. Ada yang bisa saya bantu seputar jadwal latihan, biaya, atau informasi sekolah sepatu roda kami hari ini? ✨";
        }

        // 2. Cek apakah ada di Basis Pengetahuan (Knowledge Base)
        if (!empty($knowledgeBase)) {
            $kbLines = explode("\n", $knowledgeBase);
            foreach ($kbLines as $line) {
                if (strlen(trim($line)) > 5 && str_contains(strtolower($line), $msgLower)) {
                    return "Halo Kak! 😊 Berdasarkan informasi KILAT: *" . trim($line) . "* ⛸️. Jika butuh detail lebih lanjut, silakan beri tahu saya ya! ✨";
                }
            }
        }

        // 3. Deteksi Topik Spesifik (Jadwal, Biaya, Usia, Alat)
        if (str_contains($msgLower, 'jadwal') || str_contains($msgLower, 'lokasi') || str_contains($msgLower, 'latihan')) {
            return "Tentu kak! 🗓️ Latihan rutin KILAT dilaksanakan di area Parkiran lantai 6 Kediri Mall dan lantai 2 Pasar Setono Betek (biasanya pada sore hari di hari Selasa & Jumat) ⛸️. Ada hal lain yang ingin ditanyakan? 😊";
        }

        if (str_contains($msgLower, 'biaya') || str_contains($msgLower, 'iuran') || str_contains($msgLower, 'tarif') || str_contains($msgLower, 'pembayaran')) {
            return "Baik kak, mengenai iuran bulanan dibayarkan setiap tanggal 1 sampai 10 di awal bulan ya Kak 💳. Pembayaran bisa dilakukan secara tunai ke admin di lokasi atau via transfer Bank BCA ✨. Ada yang bisa saya bantu lagi? 🙏";
        }

        if (str_contains($msgLower, 'usia') || str_contains($msgLower, 'umur') || str_contains($msgLower, 'minimal') || str_contains($msgLower, 'anak')) {
            return "Untuk usia tidak ada batasan minimal yang ketat kak! 😊 Semakin dini anak bergabung, semakin baik untuk melatih motorik dan keseimbangannya ⛸️. Kami juga menyediakan kelas untuk dewasa lho ✨!";
        }

        if (str_contains($msgLower, 'alat') || str_contains($msgLower, 'sepatu') || str_contains($msgLower, 'helm') || str_contains($msgLower, 'beli')) {
            return "Untuk sesi trial/percobaan pertama, kami menyediakan fasilitas penyewaan alat lengkap kok Kak 🛡️. Namun untuk member resmi, disarankan memiliki perlengkapan sendiri demi kenyamanan dan higienitas ⛸️. Jangan lupa konsultasikan dulu dengan pelatih sebelum membeli ya! 😊";
        }

        // 4. Default jika tidak ditemukan (Menyarankan ke bagian kontak)
        return "Mohon maaf Kak, saya belum menemukan informasi spesifik mengenai hal tersebut di data kami 🙏. Agar bisa mendapat penjelasan yang lebih akurat, silakan langsung menghubungi pengurus melalui bagian kontak di [halaman utama website](/#kontak) ya Kak ✨. Ada hal lain yang bisa saya bantu? 😊";
    }
}
