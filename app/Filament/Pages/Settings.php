<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadSettings());
    }

    protected function loadSettings(): array
    {
        $groups = Setting::groups();
        $data = [];
        foreach ($groups as $groupKey => $groupLabel) {
            $rows = Setting::where('group', $groupKey)->get();
            foreach ($rows as $row) {
                $data["{$groupKey}.{$row->key}"] = $row->value;
            }
        }
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')->tabs([
                Tab::make('General')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        TextInput::make('general.site_name')->label('Site Name')->required(),
                        TextInput::make('general.site_tagline')->label('Tagline'),
                        TextInput::make('general.site_url')->label('Site URL')->url(),
                        Select::make('general.locale')->label('Default Language')
                            ->options(['id' => 'Indonesia', 'en' => 'English'])->default('id'),
                    ]),

                Tab::make('Mail / SMTP')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        TextInput::make('smtp.host')->label('Host'),
                        TextInput::make('smtp.port')->label('Port')->numeric()->default(587),
                        TextInput::make('smtp.username')->label('Username'),
                        TextInput::make('smtp.password')->label('Password')->password()->revealable(),
                        Select::make('smtp.encryption')->label('Encryption')->options(['tls' => 'TLS', 'ssl' => 'SSL', 'null' => 'None'])->default('tls'),
                        TextInput::make('smtp.from_address')->label('From Address')->email(),
                        TextInput::make('smtp.from_name')->label('From Name'),
                    ]),

                Tab::make('WhatsApp (Baileys)')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->schema([
                        TextInput::make('baileys.api_url')->label('API URL')->placeholder('http://localhost:3000'),
                        TextInput::make('baileys.api_key')->label('API Key')->password()->revealable(),
                        TextInput::make('baileys.timeout')->label('Timeout (detik)')->numeric()->default(10),
                    ])->columns(2),

                Tab::make('Telegram Bot')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        TextInput::make('telegram.bot_token')->label('Bot Token')->password()->revealable(),
                        TextInput::make('telegram.chat_id')->label('Default Chat ID'),
                    ])->columns(2),

                Tab::make('Social Media')
                    ->icon('heroicon-o-share')
                    ->schema([
                        TextInput::make('social.github')->label('GitHub URL'),
                        TextInput::make('social.twitter')->label('Twitter / X URL'),
                        TextInput::make('social.facebook')->label('Facebook URL'),
                        TextInput::make('social.instagram')->label('Instagram URL'),
                        TextInput::make('social.youtube')->label('YouTube URL'),
                        TextInput::make('social.tiktok')->label('TikTok URL'),
                    ])->columns(2),

                Tab::make('SEO Defaults')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        Textarea::make('seo.default_meta_description')->label('Default Meta Description')->rows(2),
                        TextInput::make('seo.default_meta_keywords')->label('Default Keywords'),
                    ]),

                Tab::make('Appearance')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        FileUpload::make('appearance.site_logo')->label('Site Logo')->image()->disk('public')->directory('settings'),
                        FileUpload::make('appearance.site_favicon')->label('Favicon')->image()->disk('public')->directory('settings'),
                        TextInput::make('appearance.primary_color')->label('Primary Color (hex)')->default('#00a8f4'),
                    ])->columns(2),

                Tab::make('Exam Settings')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Toggle::make('exam.allow_retake')->label('Allow Retake')->default(true),
                        TextInput::make('exam.default_duration_minutes')->label('Default Duration (menit)')->numeric()->default(120),
                        TextInput::make('exam.default_passing_score')->label('Default Passing Score (%)')->numeric()->default(70),
                    ])->columns(2),

                Tab::make('Maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Toggle::make('maintenance.enabled')->label('Enable Maintenance Mode')->default(false),
                        Textarea::make('maintenance.message')->label('Maintenance Message')->rows(2),
                    ]),
            ])->columnSpanFull(),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            [$group, $k] = explode('.', $key, 2);
            $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string'));

            $stored = match ($type) {
                'boolean' => $value ? '1' : '0',
                'json'    => json_encode($value, JSON_UNESCAPED_UNICODE),
                'integer' => (string) $value,
                default   => is_array($value) ? json_encode($value) : (string) $value,
            };

            Setting::updateOrCreate(
                ['group' => $group, 'key' => $k],
                ['value' => $stored, 'type' => $type]
            );
        }

        Cache::flush();

        Notification::make()
            ->title('Settings disimpan!')
            ->body('Cache udah di-flush. Beberapa setting butuh refresh halaman.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Settings')
                ->submit('save'),
            Action::make('clearCache')
                ->label('Clear Cache')
                ->color('gray')
                ->action(function () {
                    Cache::flush();
                    Notification::make()->title('Cache dibersihkan!')->success()->send();
                }),
        ];
    }
}
