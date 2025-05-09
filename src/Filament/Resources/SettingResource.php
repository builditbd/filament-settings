<?php

namespace Builditbd\FilamentSettings\Filament\Resources;

use Builditbd\FilamentSettings\Filament\Resources\SettingResource\Pages;
use Builditbd\FilamentSettings\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Settings';
    // protected static ?string $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return config('filament-settings.settings_group_name');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('display_name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('details')
                            ->rows(2)
                            ->columnSpan('full'),
                    ])
                    ->columns(2),

                Select::make('type')
                    ->options([
                        'text' => 'Text',
                        'text_area' => 'Text Area',
                        'radio_btn' => 'Radio Button',
                        'checkbox' => 'Checkbox',
                        'select_dropdown' => 'Select Dropdown',
                        'file' => 'File',
                        'image' => 'Image',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('value', null)), // Clear value on type change

                Forms\Components\Component::make('value_field')
                    ->schema(function (\Filament\Forms\Get $get) {
                        $type = $get('type');
                        $fields = [];

                        switch ($type) {
                            case 'text':
                                $fields[] = TextInput::make('value');
                                break;
                            case 'text_area':
                                $fields[] = Textarea::make('value')->columnSpan('full');
                                break;
                            case 'radio_btn':
                                $fields[] = TextInput::make('value')
                                    ->helperText('Enter comma-separated options.');
                                break;
                            case 'checkbox':
                                $fields[] = Checkbox::make('value')
                                    ->label('Is Checked');
                                break;
                            case 'select_dropdown':
                                $fields[] = Textarea::make('value')
                                    ->helperText('Enter comma-separated options.');
                                break;
                            case 'file':
                                $fields[] = FileUpload::make('value')
                                    ->directory('settings')
                                    ->visibility('public');
                                break;
                            case 'image':
                                $fields[] = FileUpload::make('value')
                                    ->image()
                                    ->directory('settings')
                                    ->visibility('public');
                                break;
                        }

                        return $fields;
                    })
                    ->columnSpan('full'),

                Group::make()
                    ->schema([
                        TextInput::make('order')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('settings_group')
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('remarks')
                            ->nullable()
                            ->maxLength(255),
                        Checkbox::make('is_permanent')
                            ->label('Permanent Setting')
                            ->disabled(), // Make it non-editable
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('settings_group')
                    ->searchable()
                    ->sortable()
                    ->nullable(),
                IconColumn::make('is_permanent')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-pencil-square'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Setting $record) {
                        if ($record->is_permanent) {
                            $action->halt();
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Permanent Setting')
                                ->body('This setting cannot be deleted.')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('order');
    }
}

