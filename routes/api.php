<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['validate.api_key', 'rate_limit.api_key'])->get('/ping', function (Request $request) {
    $apiKey = $request->attributes->get('apiKey');
    $project = $request->attributes->get('project');

    return response()->json([
        'message' => 'API key accepted.',
        'project' => [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
        ],
        'api_key' => [
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key_prefix' => $apiKey->key_prefix,
            'status' => $apiKey->status,
        ],
    ]);
});
