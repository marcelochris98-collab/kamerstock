<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\ClientScoringService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateEligibleCreditClient extends Command
{
    protected $signature = 'test:create-eligible-credit-client';

    protected $description = 'Créer un client test avec 10 ventes sur plus de 2 mois pour tester le crédit';

    public function handle(ClientScoringService $scoringService): int
    {
        $product = Product::first();

        if (!$product) {
            $this->error('Aucun produit trouvé. Crée d’abord au moins un produit.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($product, $scoringService) {
            $client = Client::firstOrCreate(
                ['phone' => '699999999'],
                [
                    'name' => 'Client Test Crédit Éligible',
                    'email' => null,
                    'type' => 'particulier',
                ]
            );

            Sale::where('client_id', $client->id)->delete();

            for ($i = 0; $i < 10; $i++) {
                $date = Carbon::now()->subDays(70 - ($i * 7));

                $sale = Sale::create([
                    'user_id' => 1,
                    'client_id' => $client->id,
                    'total_amount' => 100000,
                    'amount_paid' => 100000,
                    'change_due' => 0,
                    'payment_mode' => 'cash',
                    'status' => 'completee',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 100000,
                    'subtotal' => 100000,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $scoringService->update($client);
        });

        $this->info('Client test éligible créé : 699999999');
        return self::SUCCESS;
    }
}
