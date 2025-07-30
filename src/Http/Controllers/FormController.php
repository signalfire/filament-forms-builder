<?php

namespace Signalfire\FilamentFormsBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Signalfire\FilamentFormsBuilder\Events\FormSubmitted;
use Signalfire\FilamentFormsBuilder\Models\Form;
use Signalfire\FilamentFormsBuilder\Models\FormSubmission;

class FormController extends Controller
{
    public function show(string $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with(['fields' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->firstOrFail();

        return view('filament-forms-builder::form-page', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with(['fields' => function ($query) {
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->firstOrFail();

        // Build validation rules
        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $fieldRules = $field->getValidationRulesArray();
            
            if (!empty($fieldRules)) {
                $rules[$field->key] = $fieldRules;
            }
            
            $attributes[$field->key] = $field->label;
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get all form field data, not just validated fields
        $formData = [];
        foreach ($form->fields as $field) {
            if ($request->has($field->key)) {
                $formData[$field->key] = $request->input($field->key);
            }
        }
        
        $validatedData = $formData;

        // Store submission if enabled
        if (config('filament-forms-builder.store_submissions', true)) {
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'data' => $validatedData,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'submitted_at' => now(),
            ]);
        } else {
            $submission = null;
        }

        // Dispatch event for extensibility
        event(new FormSubmitted($form, $validatedData, $submission, $request));

        // Redirect with success message
        return redirect()->back()->with('success', $form->success_message);
    }
}