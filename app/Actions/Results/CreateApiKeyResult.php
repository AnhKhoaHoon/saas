<?php

namespace App\Actions\Results;

use App\Models\ApiKey;

class CreateApiKeyResult
{
    public function __construct(
        public readonly ApiKey $apiKey,
        public readonly string $plainTextKey,
    ) {
    }
}
