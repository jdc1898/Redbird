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
}
