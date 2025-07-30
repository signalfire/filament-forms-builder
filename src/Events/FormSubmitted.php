<?php

namespace Signalfire\FilamentFormsBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;

class FormSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Form $form,
        public array $data,
        public ?FormSubmission $submission,
        public Request $request
    ) {
        //
    }
}