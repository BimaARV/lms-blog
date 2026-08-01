<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi ke baileys-server (node.js service terpisah)
 * Endpoint diset via Settings Center (bukan .env).
 *
 * Default konvensi request:
 *   POST {api_url}/send-message     body: {"to":"628xxx","message":"..."}
 *   GET  {api_url}/session/status   -> {"connected":true,"phone":"628xxx"}
 *   GET  {api_url}/session/qr       -> {"qr":"base64-png"}
 *
 * Bisa di-override sesuai engine baileys yang loe pake.
 */
class BaileysService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl  = Setting::get('api_url', 'http://localhost:3000', 'baileys') ?: 'http://localhost:3000';
        $this->apiKey  = Setting::get('api_key', '', 'baileys') ?: '';
        $this->timeout = (int) Setting::get('timeout', 10, 'baileys');
    }

    protected function client()
    {
        $c = Http::timeout($this->timeout)->acceptJson();
        if ($this->apiKey) {
            $c = $c->withToken($this->apiKey);
        }
        return $c;
    }

    /**
     * Kirim pesan teks ke nomor WA (format: 628xxx).
     */
    public function sendMessage(string $phone, string $message): array
    {
        $phone = self::normalizePhone($phone);

        try {
            $response = $this->client()->post(rtrim($this->apiUrl, '/') . '/send-message', [
                'to'      => $phone,
                'message' => $message,
            ]);

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'data'    => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Baileys send failed: ' . $e->getMessage(), ['to' => $phone]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Status koneksi session (untuk ditampilkan sebagai badge di admin).
     */
    public function status(): array
    {
        try {
            $r = $this->client()->get(rtrim($this->apiUrl, '/') . '/session/status');
            return ['success' => true, 'data' => $r->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Ambil QR buat pairing (display di admin panel).
     */
    public function getQr(): array
    {
        try {
            $r = $this->client()->get(rtrim($this->apiUrl, '/') . '/session/qr');
            return ['success' => true, 'data' => $r->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Restart session (logout & pairing ulang).
     */
    public function restart(): array
    {
        try {
            $r = $this->client()->post(rtrim($this->apiUrl, '/') . '/session/restart');
            return ['success' => true, 'data' => $r->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Normalisasi nomor hp ke format 628xxx (Buat Whatsapp).
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}
