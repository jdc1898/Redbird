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

    public function test_installation_process_includes_all_required_steps()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);
        $method = $reflection->getMethod('handle');
        $method->setAccessible(true);

        // Get the method source code to verify it includes all required steps
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Verify that the installation includes all required steps
        $this->assertStringContainsString('redbird-config', $methodSource, 'Installation should publish redbird-config');
        $this->assertStringContainsString('installPermissions', $methodSource, 'Installation should call installPermissions');
        $this->assertStringContainsString('cashier-migrations', $methodSource, 'Installation should publish cashier-migrations');
        $this->assertStringContainsString('redbird-migrations', $methodSource, 'Installation should publish redbird-migrations');
        $this->assertStringContainsString('redbird-seeders', $methodSource, 'Installation should publish redbird-seeders');
        $this->assertStringContainsString('redbird-views', $methodSource, 'Installation should publish redbird-views');
        $this->assertStringContainsString('redbird-components', $methodSource, 'Installation should publish redbird-components');
        $this->assertStringContainsString('redbird-attributes', $methodSource, 'Installation should publish redbird-attributes');
        $this->assertStringContainsString('createRequiredModels', $methodSource, 'Installation should call createRequiredModels');
        $this->assertStringContainsString('copyAttributes', $methodSource, 'Installation should call copyAttributes');
        $this->assertStringContainsString('migrate', $methodSource, 'Installation should run migrations');
        $this->assertStringContainsString('RolesAndPermissionsSeeder', $methodSource, 'Installation should run the seeder');
        $this->assertStringContainsString('installFilament', $methodSource, 'Installation should call installFilament');
        $this->assertStringContainsString('installCashier', $methodSource, 'Installation should call installCashier');
    }

    public function test_config_file_has_required_sections()
    {
        $config = include __DIR__ . '/../config/redbird.php';

        // Check that all required sections exist
        $this->assertArrayHasKey('app_name', $config, 'Config should have app_name');
        $this->assertArrayHasKey('panels', $config, 'Config should have panels section');
        $this->assertArrayHasKey('subscriptions', $config, 'Config should have subscriptions section');
        $this->assertArrayHasKey('tenancy', $config, 'Config should have tenancy section');
        $this->assertArrayHasKey('features', $config, 'Config should have features section');
        $this->assertArrayHasKey('permissions', $config, 'Config should have permissions section');
        $this->assertArrayHasKey('roles', $config, 'Config should have roles section');
        $this->assertArrayHasKey('seed', $config, 'Config should have seed section');
    }

    public function test_seeder_file_exists_and_has_correct_namespace()
    {
        $seederPath = __DIR__ . '/../database/seeders/RolesAndPermissionsSeeder.php';
        $this->assertTrue(file_exists($seederPath), 'RolesAndPermissionsSeeder should exist');

        $seederContent = file_get_contents($seederPath);
        $this->assertStringContainsString('namespace Database\\Seeders;', $seederContent, 'Seeder should have correct namespace');
        $this->assertStringContainsString('class RolesAndPermissionsSeeder', $seederContent, 'Seeder should have correct class name');
        $this->assertStringContainsString('extends Seeder', $seederContent, 'Seeder should extend Seeder');
        $this->assertStringContainsString('config(\'redbird.seed\'', $seederContent, 'Seeder should use redbird.seed config');
    }

    public function test_setup_demo_data_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('setupDemoData'), 'InstallCommand should have setupDemoData method');

        $method = $reflection->getMethod('setupDemoData');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'setupDemoData should be private');
    }

    public function test_create_or_get_user_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('createOrGetUser'), 'InstallCommand should have createOrGetUser method');

        $method = $reflection->getMethod('createOrGetUser');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'createOrGetUser should be private');
    }

    public function test_config_has_correct_role_names()
    {
        $config = include __DIR__ . '/../config/redbird.php';
        $seedConfig = $config['seed'];

        // Check that the correct role names exist
        $this->assertArrayHasKey('admin', $seedConfig, 'Config should have admin role');
        $this->assertArrayHasKey('tenant', $seedConfig, 'Config should have tenant role');
        $this->assertArrayHasKey('member', $seedConfig, 'Config should have member role');

        // Check that role names match the keys
        $this->assertEquals('admin', $seedConfig['admin']['name'], 'Admin role name should be "admin"');
        $this->assertEquals('tenant', $seedConfig['tenant']['name'], 'Tenant role name should be "tenant"');
        $this->assertEquals('member', $seedConfig['member']['name'], 'Member role name should be "member"');
    }

    public function test_check_existing_application_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('checkExistingApplication'), 'InstallCommand should have checkExistingApplication method');

        $method = $reflection->getMethod('checkExistingApplication');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'checkExistingApplication should be private');
    }

    public function test_publish_filament_resources_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('publishFilamentResources'), 'InstallCommand should have publishFilamentResources method');

        $method = $reflection->getMethod('publishFilamentResources');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'publishFilamentResources should be private');
    }

    public function test_filament_resources_exist_in_package()
    {
        $adminPath = __DIR__ . '/../src/Filament/Admin';
        $tenantPath = __DIR__ . '/../src/Filament/Tenant';
        $memberPath = __DIR__ . '/../src/Filament/Member';

        $this->assertTrue(is_dir($adminPath), 'Admin Filament resources should exist');
        $this->assertTrue(is_dir($tenantPath), 'Tenant Filament resources should exist');
        $this->assertTrue(is_dir($memberPath), 'Member Filament directory should exist (even if empty)');

        // Check that Admin has resources
        $adminResourcesPath = $adminPath . '/Resources';
        $this->assertTrue(is_dir($adminResourcesPath), 'Admin Resources directory should exist');

        // Check that Tenant has resources
        $tenantResourcesPath = $tenantPath . '/Resources';
        $this->assertTrue(is_dir($tenantResourcesPath), 'Tenant Resources directory should exist');

        // Check Member directory (may be empty in CI due to Git not tracking empty dirs)
        if (!is_dir($memberPath)) {
            $this->markTestSkipped('Member directory not found - this is expected in CI if the directory is empty and not tracked by Git');
        }
        $this->assertTrue(is_dir($memberPath), 'Member Filament directory should exist (even if empty)');
    }

    public function test_create_required_models_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('createRequiredModels'), 'InstallCommand should have createRequiredModels method');

        $method = $reflection->getMethod('createRequiredModels');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'createRequiredModels should be private');
    }

    public function test_create_required_models_includes_class_existence_check()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);
        $method = $reflection->getMethod('createRequiredModels');
        $method->setAccessible(true);

        // Get the method source code to verify it includes class existence check
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Verify that the method includes class existence check
        $this->assertStringContainsString('class_exists', $methodSource, 'createRequiredModels should check if model class already exists');
        $this->assertStringContainsString('App\\\\Models', $methodSource, 'createRequiredModels should check for App\\Models namespace');
    }

    public function test_view_components_exist_in_package()
    {
        $componentsPath = __DIR__ . '/../src/Resources/views/components';

        $this->assertTrue(is_dir($componentsPath), 'View components directory should exist');

        // Check for specific component files
        $expectedComponents = [
            'select-card.blade.php',
            'tiered-pricing.blade.php',
            'pricing-description.blade.php',
            'radio-card.blade.php',
        ];

        foreach ($expectedComponents as $component) {
            $componentPath = $componentsPath . '/' . $component;
            $this->assertTrue(file_exists($componentPath), "Component {$component} should exist");
        }

        // Check for filament subdirectory
        $filamentComponentsPath = $componentsPath . '/filament';
        $this->assertTrue(is_dir($filamentComponentsPath), 'Filament components subdirectory should exist');

        // Check for specific Filament component files
        $expectedFilamentComponents = [
            'price-description.blade.php',
            'radio-card.blade.php',
            'badge-description.blade.php',
            'hr.blade.php',
        ];

        foreach ($expectedFilamentComponents as $component) {
            $componentPath = $filamentComponentsPath . '/' . $component;
            $this->assertTrue(file_exists($componentPath), "Filament component {$component} should exist");
        }
    }

    public function test_attributes_exist_in_package()
    {
        $attributesPath = __DIR__ . '/../src/Attributes';

        $this->assertTrue(is_dir($attributesPath), 'Attributes directory should exist');

        // Check for specific attribute files
        $expectedAttributes = [
            'Context.php',
        ];

        foreach ($expectedAttributes as $attribute) {
            $attributePath = $attributesPath . '/' . $attribute;
            $this->assertTrue(file_exists($attributePath), "Attribute {$attribute} should exist");
        }

        // Check that Context attribute has correct namespace
        $contextPath = $attributesPath . '/Context.php';
        $contextContent = file_get_contents($contextPath);
        $this->assertStringContainsString('namespace Fullstack\\Redbird\\Attributes;', $contextContent, 'Context attribute should have correct package namespace');
    }

    public function test_install_cashier_method_includes_installation_check()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);
        $method = $reflection->getMethod('installCashier');
        $method->setAccessible(true);

        // Get the method source code to verify it includes Cashier installation check
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Verify that the method includes Cashier installation check
        $this->assertStringContainsString('class_exists', $methodSource, 'installCashier should check if Cashier is already installed');
        $this->assertStringContainsString('CashierServiceProvider', $methodSource, 'installCashier should check for CashierServiceProvider');
    }

    public function test_copy_attributes_method_exists()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);

        $this->assertTrue($reflection->hasMethod('copyAttributes'), 'InstallCommand should have copyAttributes method');

        $method = $reflection->getMethod('copyAttributes');
        $this->assertEquals('private', \Reflection::getModifierNames($method->getModifiers())[0], 'copyAttributes should be private');
    }

    public function test_copy_attributes_includes_class_existence_check()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass(InstallCommand::class);
        $method = $reflection->getMethod('copyAttributes');
        $method->setAccessible(true);

        // Get the method source code to verify it includes class existence check
        $filename = $reflection->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Verify that the method includes class existence check
        $this->assertStringContainsString('class_exists', $methodSource, 'copyAttributes should check if attribute class already exists');
        $this->assertStringContainsString('App\\\\Attributes', $methodSource, 'copyAttributes should check for App\\Attributes namespace');
    }
}
