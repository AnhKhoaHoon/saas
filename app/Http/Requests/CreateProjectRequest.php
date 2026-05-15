<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'timezone' => [
                'nullable',
                'string',
                'max:120',
                Rule::in(array_unique([...timezone_identifiers_list(), (string) config('app.timezone'), 'Asia/Saigon'])),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => (string) $this->string('name'),
            'description' => $this->filled('description') ? (string) $this->string('description') : null,
            // Keep settings extensible so later quotas, alerts, and environment flags can be added without reshaping the action.
            'settings' => [
                'timezone' => $this->filled('timezone') ? (string) $this->string('timezone') : config('app.timezone'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->value(),
            'description' => $this->filled('description')
                ? Str::of((string) $this->input('description'))->squish()->value()
                : null,
        ]);
    }
}
