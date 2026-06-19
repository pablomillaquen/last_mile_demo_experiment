<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function show(string $path)
    {
        $sanitized = str_replace('..', '', $path);
        $sanitized = ltrim($sanitized, '/');

        if (!preg_match('/^[a-zA-Z0-9_\-\/]+$/', $sanitized)) {
            abort(404);
        }

        $filePath = resource_path('docs/' . $sanitized . '.md');

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response(file_get_contents($filePath), 200, [
            'Content-Type' => 'text/markdown',
        ]);
    }
}
