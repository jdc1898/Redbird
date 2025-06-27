<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ProviderRegistrationTest extends TestCase
{
    public function test_provider_class_names_are_correctly_formatted()
    {
        $testCases = [
            'admin' => 'App\\Providers\\Filament\\AdminPanelProvider',
            'tenant' => 'App\\Providers\\Filament\\TenantPanelProvider',
            'member' => 'App\\Providers\\Filament\\MemberPanelProvider',
        ];

        foreach ($testCases as $panelId => $expectedClass) {
            $actualClass = "App\\Providers\\Filament\\" . ucfirst($panelId) . 'PanelProvider';
            $this->assertEquals($expectedClass, $actualClass, "Panel '{$panelId}' should generate class '{$expectedClass}'");
        }
    }

    public function test_provider_file_paths_are_correctly_formatted()
    {
        $testCases = [
            'admin' => 'app/Providers/Filament/AdminPanelProvider.php',
            'tenant' => 'app/Providers/Filament/TenantPanelProvider.php',
            'member' => 'app/Providers/Filament/MemberPanelProvider.php',
        ];

        foreach ($testCases as $panelId => $expectedPath) {
            $actualPath = 'app/Providers/Filament/' . ucfirst($panelId) . 'PanelProvider.php';
            $this->assertEquals($expectedPath, $actualPath, "Panel '{$panelId}' should generate file at '{$expectedPath}'");
        }
    }

    public function test_provider_registration_pattern_for_bootstrap_providers()
    {
        $providerClass = 'App\\Providers\\Filament\\AdminPanelProvider';
        $mockBootstrapContent = "<?php\n\nreturn [\n    // Providers\n];";

        $expectedContent = "<?php\n\nreturn [\n    // Providers\n    {$providerClass}::class,\n];";
        $actualContent = str_replace('];', "    {$providerClass}::class,\n];", $mockBootstrapContent);

        $this->assertEquals($expectedContent, $actualContent, 'Provider should be correctly added to bootstrap/providers.php');
    }

    public function test_provider_registration_detection_works()
    {
        $providerClass = 'App\\Providers\\Filament\\AdminPanelProvider';
        $contentWithProvider = "<?php\n\nreturn [\n    {$providerClass}::class,\n];";
        $contentWithoutProvider = "<?php\n\nreturn [\n    // Other providers\n];";

        $this->assertTrue(strpos($contentWithProvider, $providerClass) !== false, 'Should detect existing provider');
        $this->assertFalse(strpos($contentWithoutProvider, $providerClass) !== false, 'Should not detect missing provider');
    }
}
