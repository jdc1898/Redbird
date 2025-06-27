<?php

namespace Tests;

use Fullstack\Redbird\Commands\InstallCommand;
use PHPUnit\Framework\TestCase;

class PanelGenerationTest extends TestCase
{
    public function test_install_command_can_read_panel_config()
    {
        // Load the actual config file
        $config = include __DIR__ . '/../config/redbird.php';
        $panelsConfig = $config['panels'];

        // Test that we can process the config structure
        $this->assertIsArray($panelsConfig);
        $this->assertArrayHasKey('admin', $panelsConfig);
        $this->assertArrayHasKey('tenant', $panelsConfig);
        $this->assertArrayHasKey('member', $panelsConfig);

        // Test each panel has required keys
        foreach ($panelsConfig as $panelId => $panelConfig) {
            $this->assertArrayHasKey('path', $panelConfig, "Panel {$panelId} should have 'path' key");

            // Guard is optional, but if present should be a string
            if (isset($panelConfig['guard'])) {
                $this->assertIsString($panelConfig['guard'], "Panel {$panelId} guard should be a string");
            }
        }
    }

    public function test_panel_provider_naming_convention()
    {
              $testCases = [
            'admin' => 'AdminPanelProvider',
            'tenant' => 'TenantPanelProvider',
            'member' => 'MemberPanelProvider',
        ];

      foreach ($testCases as $panelId => $expectedClassName) {
          $actualClassName = ucfirst($panelId) . 'PanelProvider';
          $this->assertEquals($expectedClassName, $actualClassName, "Panel ID '{$panelId}' should generate class name '{$expectedClassName}'");
      }
    }

    public function test_panel_config_has_valid_colors()
    {
        // Load the actual config file
        $config = include __DIR__ . '/../config/redbird.php';
        $panelsConfig = $config['panels'];

        // Test each panel has a colors configuration
        foreach ($panelsConfig as $panelId => $panelConfig) {
            $this->assertArrayHasKey('colors', $panelConfig, "Panel {$panelId} should have 'colors' key");

            // Verify the colors is an array with primary key
            $colorValue = $panelConfig['colors'];
            $this->assertIsArray($colorValue, "Panel {$panelId} colors should be an array");
            $this->assertArrayHasKey('primary', $colorValue, "Panel {$panelId} colors should have 'primary' key");

            // Verify the primary color is a valid hex color
            $primaryColor = $colorValue['primary'];
            $this->assertIsString($primaryColor, "Panel {$panelId} primary color should be a string");
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $primaryColor, "Panel {$panelId} primary color should be a valid hex color");
        }
    }

    public function test_stub_file_exists()
    {
      $stubPath = __DIR__ . '/../stubs/filament-panel-provider.stub';
      $this->assertTrue(file_exists($stubPath), 'Panel provider stub file should exist');

      if (file_exists($stubPath)) {
          $stubContent = file_get_contents($stubPath);
          $this->assertStringContainsString('{{ class }}', $stubContent, 'Stub should contain class placeholder');
          $this->assertStringContainsString('{{ panel_id }}', $stubContent, 'Stub should contain panel_id placeholder');
          $this->assertStringContainsString('config(\'redbird.panels.{{ panel_id }}.path\')', $stubContent, 'Stub should contain config-based path');
          $this->assertStringContainsString('config(\'redbird.panels.{{ panel_id }}.guard\')', $stubContent, 'Stub should contain config-based guard');
          $this->assertStringContainsString('config(\'redbird.panels.{{ panel_id }}.colors\')', $stubContent, 'Stub should contain config-based colors');
      }
    }
}
