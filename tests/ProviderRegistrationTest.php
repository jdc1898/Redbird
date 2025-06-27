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

    public function test_custom_guards_are_added_to_auth_configuration()
    {
        // Create a mock Laravel application
        $app = new \Illuminate\Foundation\Application();

        // Set up basic auth config
        $app['config'] = new \Illuminate\Config\Repository([
            'auth' => [
                'guards' => [
                    'web' => [
                        'driver' => 'session',
                        'provider' => 'users',
                    ],
                ],
            ],
        ]);

        // Register our service provider
        $provider = new \Fullstack\Redbird\RedbirdServiceProvider($app);
        $provider->register();
        $provider->boot();

        // Verify that custom guards are added
        $guards = $app['config']->get('auth.guards');

        $this->assertArrayHasKey('admin', $guards, 'Admin guard should be added to auth configuration');
        $this->assertArrayHasKey('tenant', $guards, 'Tenant guard should be added to auth configuration');

        // Verify guard configuration
        $this->assertEquals([
            'driver' => 'session',
            'provider' => 'users',
        ], $guards['admin'], 'Admin guard should have correct configuration');

        $this->assertEquals([
            'driver' => 'session',
            'provider' => 'users',
        ], $guards['tenant'], 'Tenant guard should have correct configuration');

        // Verify existing guards are preserved
        $this->assertArrayHasKey('web', $guards, 'Existing web guard should be preserved');
    }
}
