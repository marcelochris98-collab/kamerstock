<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'shop_name'       => 'KamerStock',
                'address'         => 'Douala, Cameroun',
                'phone'           => '+237 689 703 281',
                'email'           => 'marcelochris98@gmail.com',
                'currency'        => 'FCFA',
                'tax_rate'        => 17.50,
                'invoice_prefix'  => 'FAC',
            ]
        );

        $this->command->info('✅ Paramètres initiaux créés !');
    }
}