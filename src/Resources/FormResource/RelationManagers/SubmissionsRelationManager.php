<?php

namespace Signalfire\FilamentFormsBuilder\Resources\FormResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Submission Data')
                    ->schema([
                        Forms\Components\KeyValue::make('data')
                            ->label('Form Data')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->disabled(),

                        Forms\Components\TextInput::make('ip_address')
                            ->disabled(),

                        Forms\Components\Textarea::make('user_agent')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->disabled(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('submitted_at')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('form.fields'))
            ->columns([
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('data')
                    ->label('Preview')
                    ->limit(50)
                    ->formatStateUsing(function ($record) {
                        if (empty($record->data) || !is_array($record->data)) {
                            return 'No data';
                        }
                        
                        $preview = [];
                        $count = 0;
                        
                        foreach ($record->data as $field => $value) {
                            if ($count >= 2) {
                                $preview[] = '...';
                                break;
                            }
                            
                            $displayValue = is_array($value) ? implode(', ', $value) : (string) $value;
                            $preview[] = "{$field}: {$displayValue}";
                            $count++;
                        }
                        
                        return implode(' | ', $preview);
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('submitted_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('submitted_at', today()))
                    ->label('Today'),

                Tables\Filters\Filter::make('submitted_this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),

                Tables\Filters\Filter::make('submitted_this_month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('submitted_at', now()->month))
                    ->label('This Month'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Submission Details')
                    ->modalContent(function ($record) {
                        // Try formatted data first, fallback to raw data
                        $formattedData = $record->getFormattedData();
                        $data = !empty($formattedData) ? $formattedData : ($record->data ?? []);
                        
                        $content = '<div class="space-y-4">';
                        
                        if (empty($data)) {
                            $content .= '<p class="text-gray-500">No submission data available.</p>';
                        } else {
                            foreach ($data as $field => $value) {
                                $content .= '<div class="border-b pb-2">';
                                $content .= '<dt class="font-semibold text-gray-700">' . e($field) . '</dt>';
                                
                                $displayValue = is_array($value) ? implode(', ', $value) : (string) $value;
                                $content .= '<dd class="mt-1 text-gray-900">' . e($displayValue) . '</dd>';
                                $content .= '</div>';
                            }
                        }
                        
                        $content .= '</div>';
                        
                        $content .= '<div class="mt-6 pt-4 border-t text-sm text-gray-500">';
                        $content .= '<p>Submitted: ' . $record->submitted_at->format('M j, Y \a\t g:i A') . '</p>';
                        if ($record->ip_address) {
                            $content .= '<p>IP Address: ' . e($record->ip_address) . '</p>';
                        }
                        $content .= '</div>';
                        
                        return new \Illuminate\Support\HtmlString($content);
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}