<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.key');
    }

    public function sendScoreNotification($phoneNumber, $attempt)
    {
        if (!$this->apiUrl || !$this->apiKey) {
            Log::warning('WhatsApp API not configured. Skipping notification.');
            return false;
        }

        $message = "Halo! Hasil ujian '{$attempt->exam->title}' lu udah keluar. Skor lu: {$attempt->score}/{$attempt->max_score}. Cek email buat download PDF-nya ya, pekok!";

        try {
            $response = Http::post($this->apiUrl, [
                'apiKey' => $this->apiKey,
                'recipient' => $phoneNumber,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
