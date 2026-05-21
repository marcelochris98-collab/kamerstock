<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@kamerstock.cm'],
            [
                'name'      => 'Administrateur',
                'password'  => Hash::make('Admin@2026'),
                'role_id'   => $adminRole?->id,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Compte admin créé : admin@kamerstock.cm / Admin@2026');
    }
}