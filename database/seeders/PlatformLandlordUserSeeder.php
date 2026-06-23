<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\LandlordUser;

class PlatformLandlordUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = env('LANDLORD_ADMIN_NAME', 'Super Admin KamerStock');
        $email = env('LANDLORD_ADMIN_EMAIL', 'admin@kamerstock.cm');
        $password = env('LANDLORD_ADMIN_PASSWORD', 'Admin@2026');

        LandlordUser::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
