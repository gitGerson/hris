<?php

namespace App\Filament\Resources\JobLevels;

use App\Filament\Resources\JobLevels\Pages\CreateJobLevel;
use App\Filament\Resources\JobLevels\Pages\EditJobLevel;
use App\Filament\Resources\JobLevels\Pages\ListJobLevels;
use App\Filament\Resources\JobLevels\Pages\ViewJobLevel;
use App\Filament\Resources\JobLevels\Schemas\JobLevelForm;
use App\Filament\Resources\JobLevels\Schemas\JobLevelInfolist;
use App\Filament\Resources\JobLevels\Tables\JobLevelsTable;
use App\Models\JobLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class JobLevelResource extends Resource
{
    protected static ?string $model = JobLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return JobLevelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobLevelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobLevels::route('/'),
            'create' => CreateJobLevel::route('/create'),
            'view' => ViewJobLevel::route('/{record}'),
            'edit' => EditJobLevel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
