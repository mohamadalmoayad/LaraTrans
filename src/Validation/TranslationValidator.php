<?php

namespace Almoayad\LaraTrans\Validation;

use Almoayad\LaraTrans\Validation\Concerns\ValidatesUniqueTranslations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Almoayad\LaraTrans\Models\BaseTranslation;
use Almoayad\LaraTrans\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TranslationValidator
{
    use ValidatesUniqueTranslations;

    protected array $rules;
    protected array $messages;
    protected BaseTranslation $translationModel;
    protected ?Model $model = null;

    public function __construct()
    {
        $this->initializeTranslationModel();
        $this->initializeRules();
        $this->initializeMessages();
    }

    public function setModel(Model $model): self
    {
        if (!in_array(HasTranslations::class, class_uses_recursive($model))) {
            throw new \InvalidArgumentException(
                'Model must use HasTranslations trait'
            );
        }

        $this->model = $model;
        return $this;
    }

    protected function initializeTranslationModel(): void
    {
        $modelClass = config('laratrans.storage.mode') === 'single_table'
            ? config('laratrans.models.translation')
            : config('laratrans.models.dedicated_translation');

        $this->translationModel = new $modelClass;
    }

    protected function initializeRules(): void
    {
        $this->rules = [
            'translations' => 'array',
            'translations.*.locale' => $this->translationModel->rules()['locale'],
            'translations.*.property_name' => $this->translationModel->rules()['property_name'],
            'translations.*.value' => $this->translationModel->rules()['value'],
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

    public function validate(array $data, bool $validateUnique = true): void
    {
        $validator = Validator::make($data, $this->rules, $this->messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($validateUnique) {
            $this->validateUniqueTranslations($data);
        }

        $this->validateRequiredLocales($data);
        $this->validatePropertyRequiredLocales($data);
        $this->validatePropertySpecificRules($data);
    }

    protected function validateRequiredLocales(array $data): void
    {
        $translations = $data['translations'] ?? [];
        $isSingleTranslation = count($translations) === 1;

        // Skip required locales check for single translation operations
        if ($isSingleTranslation) {
            return;
        }

        $requiredLocales = config('laratrans.validation.default_rules.required_locales', []);
        if (empty($requiredLocales)) {
            return;
        }

        $providedLocales = collect($translations)->pluck('locale')->unique()->toArray();
        $missingLocales = array_diff($requiredLocales, $providedLocales);

        if (!empty($missingLocales)) {
            throw ValidationException::withMessages([
                'translations' => ['Missing required translations for locales: ' . implode(', ', $missingLocales)],
            ]);
        }
    }

    protected function validatePropertyRequiredLocales(array $data): void
    {
        $translations = $data['translations'] ?? [];
        $isSingleTranslation = count($translations) === 1;

        // Skip property required locales check for single translation operations
        if ($isSingleTranslation) {
            return;
        }

        $errors = [];
        $groupedData = collect($translations)->groupBy('property_name')->toArray();

        foreach ($groupedData as $property => $propertyTranslations) {
            $requiredLocales = config("laratrans.validation.properties.$property.required_locales", []);
            if (empty($requiredLocales)) {
                continue;
            }

            $locales = collect($propertyTranslations)->pluck('locale')->unique()->toArray();
            $missingLocales = array_diff($requiredLocales, $locales);

            if (!empty($missingLocales)) {
                $errors[] = "Missing required translations for property '{$property}' in locales: " . implode(', ', $missingLocales);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'translations' => $errors
            ]);
        }
    }

    protected function validatePropertySpecificRules(array $data): void
    {
        $propertyRules = config('laratrans.validation.properties', []);
        $errors = [];

        foreach ($data['translations'] ?? [] as $index => $translation) {
            $property = $translation['property_name'] ?? null;
            if (!$property || !isset($propertyRules[$property])) {
                continue;
            }

            $rules = $propertyRules[$property];
            $validator = Validator::make($translation, [
                'value' => array_merge(
                    $this->translationModel->rules()['value'],
                    [
                        'min:' . ($rules['min'] ?? config('laratrans.validation.default_rules.min')),
                        'max:' . ($rules['max'] ?? config('laratrans.validation.default_rules.max')),
                    ]
                ),
            ]);

            if ($validator->fails()) {
                $errors["translations.$index.value"] = $validator->errors()->first('value');
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}