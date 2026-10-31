<?php

namespace App\Filament\Support;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * صفحة إعدادات عامة: بتقرا وتحفظ مجموعة مفاتيح من جدول settings.
 */
abstract class SettingsPage extends Page
{
    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @return array<string, mixed> المفاتيح وقيمها الافتراضية */
    abstract protected function defaults(): array;

    public function mount(): void
    {
        $stored = Setting::allCached();

        $this->form->fill(collect($this->defaults())
            ->map(fn ($default, $key) => $stored[$key] ?? $default)
            ->all());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('حفظ التغييرات')->submit('save')->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (array_keys($this->defaults()) as $key) {
            Setting::put($key, $state[$key] ?? null);
        }

        Notification::make()->title('تم الحفظ — التغييرات ظهرت على الموقع')->success()->send();
    }
}
