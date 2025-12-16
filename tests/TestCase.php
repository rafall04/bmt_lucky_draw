<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake storage for tests
        Storage::fake('local');
        
        // Create empty Vite manifest for testing to avoid manifest not found errors
        $manifestPath = public_path('build/manifest.json');
        $manifestDir = dirname($manifestPath);
        
        if (!is_dir($manifestDir)) {
            mkdir($manifestDir, 0755, true);
        }
        
        if (!file_exists($manifestPath)) {
            file_put_contents($manifestPath, json_encode([
                'resources/css/app.css' => [
                    'file' => 'assets/app.css',
                    'src' => 'resources/css/app.css',
                    'isEntry' => true,
                ],
                'resources/js/app.js' => [
                    'file' => 'assets/app.js',
                    'src' => 'resources/js/app.js',
                    'isEntry' => true,
                ],
            ], JSON_PRETTY_PRINT));
        }
    }
}

