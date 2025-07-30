<?php

namespace Signalfire\FilamentFormsBuilder\Resources\FormResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Signalfire\FilamentFormsBuilder\Resources\FormResource;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}