<?php

namespace Signalfire\FilamentFormsBuilder\Resources\FormResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected static ?string $recordTitleAttribute = 'label';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Field Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                return $rule->where('form_id', $this->getOwnerRecord()->id);
                            })
                            ->helperText('Unique identifier for this field. Only letters, numbers, dashes and underscores allowed.'),

                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set, Forms\Get $get) {
                                if ($context === 'create' && empty($get('key'))) {
                                    $set('key', \Illuminate\Support\Str::slug($state, '_'));
                                }
                            }),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(config('filament-forms-builder.field_types'))
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Clear options when changing field type
                                if (!in_array($state, ['select', 'radio', 'checkbox'])) {
                                    $set('options', null);
                                }
                            }),

                        Forms\Components\Select::make('column_span')
                            ->options(config('filament-forms-builder.column_spans'))
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Field Options')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->required(),
                                Forms\Components\TextInput::make('label')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox']))
                            ->helperText('Define the available options for select, radio, or checkbox fields.'),

                        Forms\Components\TextInput::make('default_value')
                            ->maxLength(255)
                            ->helperText('Default value for this field.'),

                        Forms\Components\TextInput::make('placeholder')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['text', 'textarea', 'email', 'number']))
                            ->helperText('Placeholder text shown in the field.'),

                        Forms\Components\Textarea::make('help_text')
                            ->maxLength(500)
                            ->helperText('Additional help text displayed below the field.'),
                    ]),

                Forms\Components\Section::make('Validation & Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->default(true),

                        Forms\Components\Group::make([
                            Forms\Components\Checkbox::make('is_required')
                                ->label('Required')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'required') : false),
                                
                            Forms\Components\Checkbox::make('is_numeric')
                                ->label('Numeric')
                                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['number', 'text']))
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'numeric') : false),
                        ])
                        ->columns(1),

                        Forms\Components\Group::make([
                            Forms\Components\Checkbox::make('is_email')
                                ->label('Valid Email')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'email')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'email') : false),
                                
                            Forms\Components\Checkbox::make('is_url')
                                ->label('Valid URL')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'text')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'url') : false),
                                
                            Forms\Components\Checkbox::make('is_alpha')
                                ->label('Letters Only')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'text')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'alpha') : false),
                                
                            Forms\Components\Checkbox::make('is_alpha_num')
                                ->label('Letters & Numbers Only')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'text')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record ? str_contains($record->validation_rules ?? '', 'alpha_num') : false),
                        ])
                        ->columns(1)
                        ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'email'])),

                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('min_length')
                                ->label('Min Length')
                                ->numeric()
                                ->minValue(0)
                                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'email']))
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(function ($record) {
                                    if (!$record || !$record->validation_rules) return null;
                                    preg_match('/min:(\d+)/', $record->validation_rules, $matches);
                                    return $matches[1] ?? null;
                                }),
                                
                            Forms\Components\TextInput::make('max_length')
                                ->label('Max Length')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'email']))
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(function ($record) {
                                    if (!$record || !$record->validation_rules) return null;
                                    preg_match('/max:(\d+)/', $record->validation_rules, $matches);
                                    return $matches[1] ?? null;
                                }),
                        ])
                        ->columns(2)
                        ->hidden(fn (Forms\Get $get) => !in_array($get('type'), ['text', 'textarea', 'email']))
                        ->dehydrated(false),

                        Forms\Components\Group::make([
                            Forms\Components\Checkbox::make('use_regex')
                                ->label('Custom Regex Pattern')
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(function ($record) {
                                    if (!$record || !$record->validation_rules) return false;
                                    return str_contains($record->validation_rules, 'regex:');
                                }),
                                
                            Forms\Components\TextInput::make('regex_pattern')
                                ->label('Regex Pattern')
                                ->placeholder('/^[A-Z][a-z]+$/')
                                ->helperText('Enter a valid regex pattern including delimiters (e.g., /^[A-Z][a-z]+$/)')
                                ->visible(fn (Forms\Get $get) => $get('use_regex'))
                                ->live()
                                ->dehydrated(false)
                                ->formatStateUsing(function ($record) {
                                    if (!$record || !$record->validation_rules) return null;
                                    preg_match('/regex:(.+?)(?:\||$)/', $record->validation_rules, $matches);
                                    return $matches[1] ?? null;
                                }),
                        ])
                        ->columns(1)
                        ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'email']))
                        ->dehydrated(false),

                        Forms\Components\Hidden::make('validation_rules')
                            ->dehydrateStateUsing(function (Forms\Get $get) {
                                $rules = [];
                                
                                if ($get('is_required')) {
                                    $rules[] = 'required';
                                }
                                
                                if ($get('is_email') && $get('type') === 'email') {
                                    $rules[] = 'email';
                                }
                                
                                if ($get('is_numeric') && in_array($get('type'), ['number', 'text'])) {
                                    $rules[] = 'numeric';
                                }
                                
                                if ($get('is_url') && $get('type') === 'text') {
                                    $rules[] = 'url';
                                }
                                
                                if ($get('is_alpha') && $get('type') === 'text') {
                                    $rules[] = 'alpha';
                                }
                                
                                if ($get('is_alpha_num') && $get('type') === 'text') {
                                    $rules[] = 'alpha_num';
                                }
                                
                                if ($get('min_length') && in_array($get('type'), ['text', 'textarea', 'email'])) {
                                    $rules[] = 'min:' . $get('min_length');
                                }
                                
                                if ($get('max_length') && in_array($get('type'), ['text', 'textarea', 'email'])) {
                                    $rules[] = 'max:' . $get('max_length');
                                }
                                
                                if ($get('use_regex') && $get('regex_pattern') && in_array($get('type'), ['text', 'textarea', 'email'])) {
                                    $rules[] = 'regex:' . $get('regex_pattern');
                                }
                                
                                return !empty($rules) ? implode('|', $rules) : null;
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => config("filament-forms-builder.field_types.{$state}", ucfirst($state))),

                Tables\Columns\TextColumn::make('column_span')
                    ->label('Width')
                    ->formatStateUsing(fn (int $state): string => config("filament-forms-builder.column_spans.{$state}", "Span {$state}")),

                Tables\Columns\TextColumn::make('validation_rules')
                    ->label('Required')
                    ->formatStateUsing(fn ($state) => str_contains($state ?? '', 'required') ? '✓' : ''),

                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label('Visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(config('filament-forms-builder.field_types')),

                Tables\Filters\Filter::make('required')
                    ->label('Required')
                    ->query(fn (Builder $query): Builder => $query->where('validation_rules', 'like', '%required%'))
                    ->toggle(),

                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = $this->getOwnerRecord()->fields()->max('sort_order') + 1;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}