<?php

namespace App\Filament\Pages;

use App\Services\BaileysService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BaileysControl extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'WhatsApp Gateway';

    protected static ?string $title = 'WhatsApp Gateway Control';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.baileys-control';

    public array $status = [];
    public ?string $qrBase64 = null;
    public ?string $lastError = null;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        $svc = app(BaileysService::class);
        $this->status = $svc->status();

        if (!$this->status['success'] ?? false) {
            $this->lastError = $this->status['error'] ?? 'Unknown error';
        } else {
            $this->lastError = null;
        }
    }

    public function fetchQr(): void
    {
        $svc = app(BaileysService::class);
        $result = $svc->getQr();
        if ($result['success'] ?? false) {
            $this->qrBase64 = $result['data']['qr'] ?? null;
            $this->lastError = null;
        } else {
            $this->lastError = $result['error'] ?? 'Gagal ambil QR';
            $this->qrBase64 = null;
        }
    }

    public function restartSession(): void
    {
        $svc = app(BaileysService::class);
        $result = $svc->restart();
        if ($result['success'] ?? false) {
            Notification::make()->title('Session di-restart!')->success()->send();
            $this->qrBase64 = null;
            $this->refreshStatus();
        } else {
            Notification::make()->title('Gagal restart')->body($result['error'] ?? '')->danger()->send();
        }
    }

    public function sendTestMessage(): void
    {
        $svc = app(BaileysService::class);
        $result = $svc->sendMessage(
            request()->input('phone', '628123456789'),
            request()->input('message', 'Test dari DIYBIMA Blog, Bos! 🚀')
        );

        if ($result['success'] ?? false) {
            Notification::make()->title('Pesan terkirim!')->success()->send();
        } else {
            Notification::make()->title('Gagal kirim')->body($result['error'] ?? '')->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')->label('Refresh')->action(fn () => $this->refreshStatus()),
            Action::make('restart')->label('Restart Session')->color('danger')->requiresConfirmation()->action(fn () => $this->restartSession()),
        ];
    }
}
