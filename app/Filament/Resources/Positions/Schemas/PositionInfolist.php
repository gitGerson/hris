<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Models\Position;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PositionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /** One column at the top level, so sections stack like the form. */
            ->columns(1)
            ->components([
                Section::make('Identity')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('code')
                            ->copyable(),
                        TextEntry::make('name'),
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ]),
                Section::make('Record')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime()
                            ->visible(fn (Position $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
