<?php

namespace App\Filament\Pages;

use App\Models\Company;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Single company install: this page shows the one company row, edited through a modal.
 *
 * @property-read Schema $infolist
 */
class CompanyProfile extends Page
{
    protected string $view = 'filament.pages.company-profile';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Company';

    protected static ?string $title = 'Company';

    public ?Company $record = null;

    public function mount(): void
    {
        $this->record = Company::current();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->modalHeading('Edit company profile')
                ->modalSubmitActionLabel('Save changes')
                ->modalWidth('3xl')
                ->fillForm(fn (): array => $this->record->attributesToArray())
                ->schema($this->getFormSchema())
                ->action(function (array $data): void {
                    $this->record->update($data);
                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Company profile saved')
                        ->send();
                }),
        ];
    }

    public function defaultInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->record($this->record);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->placeholder('-'),
                        TextEntry::make('name')
                            ->placeholder('-'),
                        TextEntry::make('legal_name')
                            ->label('Legal name')
                            ->placeholder('-'),
                        TextEntry::make('tax_id')
                            ->label('Tax ID')
                            ->placeholder('-'),
                    ]),
                Section::make('Contact')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email address')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->placeholder('-'),
                        TextEntry::make('website')
                            ->placeholder('-'),
                    ]),
                Section::make('Address')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('city')
                            ->placeholder('-'),
                        TextEntry::make('province')
                            ->placeholder('-'),
                        TextEntry::make('postal_code')
                            ->placeholder('-'),
                        TextEntry::make('country')
                            ->placeholder('-'),
                    ]),
                Section::make('Branding')
                    ->schema([
                        ImageEntry::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->placeholder('No logo uploaded'),
                    ]),
            ]);
    }

    /**
     * Shared by the edit modal.
     *
     * @return array<mixed>
     */
    protected function getFormSchema(): array
    {
        return [
            Section::make('Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->helperText('Short unique key used by imports and exports.')
                        ->required()
                        ->maxLength(255)
                        ->unique(table: Company::class, ignorable: fn (): ?Company => $this->record),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('legal_name')
                        ->label('Legal name')
                        ->helperText('Registered entity name, used on payroll documents.')
                        ->maxLength(255),
                    TextInput::make('tax_id')
                        ->label('Tax ID')
                        ->maxLength(255),
                ]),
            Section::make('Contact')
                ->columns(3)
                ->schema([
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('website')
                        ->url()
                        ->maxLength(255),
                ]),
            Section::make('Address')
                ->columns(2)
                ->schema([
                    Textarea::make('address')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('city')
                        ->maxLength(255),
                    TextInput::make('province')
                        ->maxLength(255),
                    TextInput::make('postal_code')
                        ->maxLength(20),
                    Select::make('country')
                        ->options([
                            'ID' => 'Indonesia',
                            'SG' => 'Singapore',
                            'MY' => 'Malaysia',
                        ])
                        ->default('ID')
                        ->required(),
                ]),
            Section::make('Branding')
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('companies')
                        ->visibility('public'),
                ]),
        ];
    }
}
