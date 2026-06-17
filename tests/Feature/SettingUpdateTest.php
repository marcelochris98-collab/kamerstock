<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingUpdateTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $settings;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create admin role and user
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        // Create initial settings
        $this->settings = Setting::create([
            'id' => 1,
            'shop_name' => 'Initial Shop',
            'currency' => 'USD',
            'tax_rate' => 10,
            'invoice_prefix' => 'INV',
        ]);
    }

    public function test_admin_can_update_settings_and_change_logo_at_will()
    {
        $this->actingAs($this->admin);

        // 1. First upload using POST (our new default route method)
        $logo1 = UploadedFile::fake()->image('logo1.png', 100, 100);
        $response = $this->post(route('admin.settings.update'), [
            'shop_name' => 'Updated Shop',
            'currency' => 'EUR',
            'tax_rate' => 15,
            'invoice_prefix' => 'FAC',
            'logo' => $logo1,
        ]);

        $response->assertStatus(302);
        
        $this->settings->refresh();
        $this->assertEquals('Updated Shop', $this->settings->shop_name);
        $this->assertNotNull($this->settings->logo);
        Storage::disk('public')->assertExists($this->settings->logo);
        
        $firstLogoPath = $this->settings->logo;

        // 2. Second upload (updating logo "at will") using PUT to verify backward compatibility
        $logo2 = UploadedFile::fake()->image('logo2.png', 100, 100);
        $response = $this->put(route('admin.settings.update'), [
            'shop_name' => 'Updated Shop Again',
            'currency' => 'EUR',
            'tax_rate' => 15,
            'invoice_prefix' => 'FAC',
            'logo' => $logo2,
        ]);

        $response->assertStatus(302);

        $this->settings->refresh();
        $this->assertEquals('Updated Shop Again', $this->settings->shop_name);
        $this->assertNotNull($this->settings->logo);
        $this->assertNotEquals($firstLogoPath, $this->settings->logo);
        
        // Assert new logo exists
        Storage::disk('public')->assertExists($this->settings->logo);
        
        // Assert old logo is deleted
        Storage::disk('public')->assertMissing($firstLogoPath);
    }

    public function test_admin_can_remove_logo()
    {
        $this->actingAs($this->admin);

        // Upload a logo first
        $logo = UploadedFile::fake()->image('logo.png', 100, 100);
        $this->post(route('admin.settings.update'), [
            'shop_name' => 'Shop Name',
            'currency' => 'EUR',
            'tax_rate' => 15,
            'invoice_prefix' => 'FAC',
            'logo' => $logo,
        ]);

        $this->settings->refresh();
        $logoPath = $this->settings->logo;
        $this->assertNotNull($logoPath);
        Storage::disk('public')->assertExists($logoPath);

        // Now remove the logo
        $response = $this->post(route('admin.settings.update'), [
            'shop_name' => 'Shop Name',
            'currency' => 'EUR',
            'tax_rate' => 15,
            'invoice_prefix' => 'FAC',
            'remove_logo' => '1',
        ]);

        $response->assertStatus(302);

        $this->settings->refresh();
        $this->assertNull($this->settings->logo);
        Storage::disk('public')->assertMissing($logoPath);
    }
}
