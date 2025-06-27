<?php

namespace Tests;

use Fullstack\Redbird\Commands\InstallCommand;
use PHPUnit\Framework\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_install_command_exists()
    {
        $this->assertTrue(class_exists(InstallCommand::class), 'InstallCommand should exist');
    }

    public function test_install_command_has_correct_signature()
    {
        $command = new InstallCommand();

        $this->assertStringContainsString('redbird:install', $command->getName());
        $this->assertEquals('Install the Redbird SaaS package', $command->getDescription());
    }

    public function test_install_command_extends_laravel_command()
    {
        $reflection = new \ReflectionClass(InstallCommand::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Console\Command::class));
    }

    public function test_install_filament_method_includes_asset_publishing()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);
        $method = $reflection->getMethod('installFilament');
        $method->setAccessible(true);

        // Get the method source code to verify it includes asset publishing
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Verify that the method includes Filament asset publishing
        $this->assertStringContainsString('filament-assets', $methodSource, 'installFilament should publish filament-assets');
        $this->assertStringContainsString('filament-config', $methodSource, 'installFilament should publish filament-config');
    }
}
