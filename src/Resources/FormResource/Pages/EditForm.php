<?php

namespace Signalfire\FilamentFormsBuilder\Resources\FormResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Signalfire\FilamentFormsBuilder\Resources\FormResource;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview Form')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => $this->getRecord()->getPublicUrl())
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }
}