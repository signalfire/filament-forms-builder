<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
    @if($form->description)
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $form->name }}</h2>
            <p class="text-gray-600">{{ $form->description }}</p>
        </div>
    @else
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $form->name }}</h2>
    @endif

    <form method="POST" action="{{ $form->getSubmissionUrl() }}" class="space-y-6" novalidate>
        @csrf
        
        <div class="grid {{ $component->getColumnClass() }} gap-6">
            @foreach($form->fields as $field)
                <div class="{{ $component->getFieldColumnSpan($field->column_span) }}">
                    <div class="form-field">
                        <label for="field_{{ $field->key }}" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $field->label }}
                            @if($field->is_required)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @switch($field->type)
                            @case('text')
                            @case('email')
                            @case('number')
                                <input 
                                    type="{{ $field->type }}"
                                    id="field_{{ $field->key }}"
                                    name="{{ $field->key }}"
                                    value="{{ $component->getFieldValue($field->key) ?? $field->default_value }}"
                                    placeholder="{{ $field->placeholder }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @if($component->hasError($field->key)) border-red-500 @endif"
                                    @if($field->is_required) required @endif
                                />
                                @break

                            @case('textarea')
                                <textarea 
                                    id="field_{{ $field->key }}"
                                    name="{{ $field->key }}"
                                    placeholder="{{ $field->placeholder }}"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @if($component->hasError($field->key)) border-red-500 @endif"
                                    @if($field->is_required) required @endif
                                >{{ $component->getFieldValue($field->key) ?? $field->default_value }}</textarea>
                                @break

                            @case('select')
                                <select 
                                    id="field_{{ $field->key }}"
                                    name="{{ $field->key }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @if($component->hasError($field->key)) border-red-500 @endif"
                                    @if($field->is_required) required @endif
                                >
                                    <option value="">Select an option...</option>
                                    @foreach($field->getOptionsForSelect() as $value => $label)
                                        <option value="{{ $value }}" @if(($component->getFieldValue($field->key) ?? $field->default_value) == $value) selected @endif>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @break

                            @case('radio')
                                <div class="space-y-2">
                                    @foreach($field->getOptionsForSelect() as $value => $label)
                                        <label class="flex items-center">
                                            <input 
                                                type="radio"
                                                name="{{ $field->key }}"
                                                value="{{ $value }}"
                                                @if(($component->getFieldValue($field->key) ?? $field->default_value) == $value) checked @endif
                                                class="mr-2 text-blue-600 focus:ring-blue-500"
                                                @if($field->is_required) required @endif
                                            />
                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('checkbox')
                                @if(!empty($field->options))
                                    <div class="space-y-2">
                                        @foreach($field->getOptionsForSelect() as $value => $label)
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox"
                                                    name="{{ $field->key }}[]"
                                                    value="{{ $value }}"
                                                    @if(is_array($component->getFieldValue($field->key)) && in_array($value, $component->getFieldValue($field->key))) checked @endif
                                                    class="mr-2 text-blue-600 focus:ring-blue-500 rounded"
                                            @if($field->is_required) required @endif
                                                />
                                                <span class="text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox"
                                            id="field_{{ $field->key }}"
                                            name="{{ $field->key }}"
                                            value="1"
                                            @if($component->getFieldValue($field->key) == '1' || $field->default_value == '1') checked @endif
                                            class="mr-2 text-blue-600 focus:ring-blue-500 rounded"
                                            @if($field->is_required) required @endif
                                        />
                                        <span class="text-sm text-gray-700">{{ $field->label }}</span>
                                    </label>
                                @endif
                                @break

                            @case('date')
                                <input 
                                    type="date"
                                    id="field_{{ $field->key }}"
                                    name="{{ $field->key }}"
                                    value="{{ $component->getFieldValue($field->key) ?? $field->default_value }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @if($component->hasError($field->key)) border-red-500 @endif"
                                    @if($field->is_required) required @endif
                                />
                                @break
                        @endswitch

                        @if($field->help_text)
                            <p class="mt-1 text-sm text-gray-500">{{ $field->help_text }}</p>
                        @endif

                        @if($component->hasError($field->key))
                            <p class="mt-1 text-sm text-red-600">{{ $component->getError($field->key) }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end pt-6 border-t border-gray-200">
            <button 
                type="submit"
                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
            >
                {{ $form->submit_button_text }}
            </button>
        </div>
    </form>
</div>