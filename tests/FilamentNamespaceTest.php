<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class FilamentNamespaceTest extends TestCase
{
    public function test_all_filament_files_have_correct_namespace()
    {
        $filamentDir = __DIR__ . '/../src/Filament';

        if (!is_dir($filamentDir)) {
            $this->markTestSkipped('Filament directory does not exist');
        }

        $phpFiles = $this->getPhpFiles($filamentDir);

        $this->assertGreaterThan(0, count($phpFiles), 'Should find PHP files in Filament directory');

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(__DIR__ . '/../src/', '', $file);

            // Check that namespace starts with Fullstack\Redbird\Filament
            $this->assertStringContainsString(
                'namespace Fullstack\Redbird\Filament',
                $content,
                "File {$relativePath} should have correct namespace"
            );

            // Check that it doesn't have the old App\Filament namespace
            $this->assertStringNotContainsString(
                'namespace App\Filament',
                $content,
                "File {$relativePath} should not have old App\\Filament namespace"
            );
        }
    }

    public function test_sample_filament_classes_exist()
    {
        // Test some specific classes to ensure they're properly namespaced
        $testClasses = [
            'Fullstack\Redbird\Filament\Admin\Forms\PriceForm',
            'Fullstack\Redbird\Filament\Tenant\Resources\OrderResource',
        ];

        foreach ($testClasses as $class) {
            $this->assertTrue(
                class_exists($class),
                "Class {$class} should exist with correct namespace"
            );
        }
    }

    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
