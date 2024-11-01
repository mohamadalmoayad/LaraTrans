<?php

namespace Almoayad\LaraTrans\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TranslationValidator
{
    protected array $rules;
    protected array $messages;

    public function __construct()
    {
        $this->initializeRules();
        $this->initializeMessages();
    }

    protected function initializeRules(): void
    {
        $this->rules = [
            'translations' => 'array',
            'translations.*.locale' => [
                'required',
                'string',
                'in:' . implode(',', config('laratrans.locales.supported')),
            ],
            'translations.*.property_name' => 'required|string',
            'translations.*.value' => [
                'required',
                'string',
                'min:' . config('laratrans.validation.default_rules.min', 1),
                'max:' . config('laratrans.validation.default_rules.max', 255),
            ],
        ];
    }

    protected function initializeMessages(): void
    {
        $this->messages = [
            'translations.*.locale.in' => 'The locale :input is not supported.',
            'translations.*.value.min' => 'The :attribute must be at least :min characters.',
            'translations.*.value.max' => 'The :attribute must not exceed :max characters.',
        ];
    }

    public function validate(array $data): void
    {
        $validator = Validator::make($data, $this->rules, $this->messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->validateRequiredLocales($data);
        $this->validatePropertySpecificRules($data);
    }

    protected function validateRequiredLocales(array $data): void
    {
        $requiredLocales = config('laratrans.validation.default_rules.required_locales', []);
        if (empty($requiredLocales)) {
            return;
        }

        $providedLocales = collect($data['translations'] ?? [])->pluck('locale')->unique()->toArray();
        $missingLocales = array_diff($requiredLocales, $providedLocales);

        if (!empty($missingLocales)) {
            throw ValidationException::withMessages([
                'translations' => ['Missing required translations for locales: ' . implode(', ', $missingLocales)],
            ]);
        }
    }

    protected function validatePropertySpecificRules(array $data): void
    {
        $propertyRules = config('laratrans.validation.properties', []);

        foreach ($data['translations'] ?? [] as $index => $translation) {
            $property = $translation['property_name'] ?? null;
            if (!$property || !isset($propertyRules[$property])) {
                continue;
            }

            $rules = $propertyRules[$property];
            $validator = Validator::make($translation, [
                'value' => [
                    'required',
                    'string',
                    'min:' . ($rules['min'] ?? config('laratrans.validation.default_rules.min')),
                    'max:' . ($rules['max'] ?? config('laratrans.validation.default_rules.max')),
                ],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }
}