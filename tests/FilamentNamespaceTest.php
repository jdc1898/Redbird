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

            // Check that namespace starts with App\Filament
            $this->assertStringContainsString(
                'namespace App\Filament',
                $content,
                "File {$relativePath} should have correct namespace"
            );

            // Check that it doesn't have the old Fullstack\Redbird\Filament namespace
            $this->assertStringNotContainsString(
                'namespace Fullstack\Redbird\Filament',
                $content,
                "File {$relativePath} should not have old Fullstack\\Redbird\\Filament namespace"
            );
        }
    }

    public function test_sample_filament_classes_exist()
    {
        // Test some specific files to ensure they're properly namespaced
        $testFiles = [
            __DIR__ . '/../src/Filament/Admin/Forms/PriceForm.php',
            __DIR__ . '/../src/Filament/Tenant/Resources/OrderResource.php',
        ];

        foreach ($testFiles as $file) {
            $this->assertFileExists($file, "File {$file} should exist");
            $content = file_get_contents($file);
            $this->assertStringContainsString(
                'namespace App\\Filament',
                $content,
                "File {$file} should have correct App namespace"
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
