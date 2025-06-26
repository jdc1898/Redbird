<?php

namespace Tests;

use Filament\Commands\MakePanelCommand;
use Filament\Facades\Filament;
use Filament\FilamentManager;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Console\Command;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FilamanetTest extends TestCase
{

  /**
   * @covers \Filament\Facades\Filament
   */
  public function test_it_confirms_filament_is_installed()
  {
    $this->assertTrue(class_exists(Filament::class), 'Filament facade class should exist');

    $this->assertTrue(class_exists(FilamentManager::class), 'FilamentManager class should exist');
    $this->assertTrue(class_exists(Panel::class), 'Panel class should exist');
    $this->assertTrue(class_exists(PanelProvider::class), 'PanelProvider class should exist');

    $this->assertTrue(method_exists(Filament::class, 'getFacadeAccessor'), 'Filament facade should have getFacadeAccessor method');
  }

  public function test_filament_panel_command_exists()
  {
    // Test that the MakePanelCommand exists
    $this->assertTrue(class_exists(MakePanelCommand::class), 'MakePanelCommand should exist');

    // Test that the command has the expected properties
    $reflection = new ReflectionClass(MakePanelCommand::class);
    $this->assertTrue($reflection->hasProperty('signature'), 'Command should have signature property');
    $this->assertTrue($reflection->hasProperty('description'), 'Command should have description property');

    // Test that it extends the base Command class
    $this->assertTrue($reflection->isSubclassOf(Command::class), 'Command should extend Illuminate\Console\Command');
  }
}
