<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateApiKeyRequest extends FormRequest
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
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'quota_limit' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'scopes' => ['nullable', 'string', 'max:255'],
            'ip_whitelist' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => (string) $this->string('name'),
            'rate_limit_per_minute' => $this->integer('rate_limit_per_minute') ?: 60,
            'quota_limit' => $this->filled('quota_limit') ? (int) $this->input('quota_limit') : null,
            // Normalize free-form scope input into an array now so the action receives predictable data.
            'scopes' => $this->parseList((string) $this->input('scopes', 'read')) ?: ['read'],
            // Keep the whitelist as an array early so later middleware can consume it without reparsing strings.
            'ip_whitelist' => $this->filled('ip_whitelist') ? $this->parseList((string) $this->input('ip_whitelist')) : null,
            'expires_at' => $this->filled('expires_at') ? (string) $this->input('expires_at') : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->value(),
            'scopes' => $this->filled('scopes')
                ? Str::of((string) $this->input('scopes'))->lower()->replace("\n", ',')->replace(';', ',')->squish()->value()
                : null,
            'ip_whitelist' => $this->filled('ip_whitelist')
                ? Str::of((string) $this->input('ip_whitelist'))->replace("\n", ',')->replace(';', ',')->squish()->value()
                : null,
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function parseList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
