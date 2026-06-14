<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Client;
use App\Models\SaleDetail;
use App\Models\CreditSale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AiService
{
    protected $provider;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'local'); // 'local', 'openai', 'gemini'
        $this->apiKey = config('services.ai.key');
        $this->model = config('services.ai.model');
    }

    /**
     * Predire le nombre de jours restants avant la rupture de stock d'un produit.
     */
    public function predictStockAlert(Product $product): array
    {
        // 1. Calculer la vélocité locale (heuristique de base)
        $thirtyDaysAgo = now()->subDays(30);
        $totalSoldInMonth = SaleDetail::where('product_id', $product->id)
            ->whereHas('sale', function($q) use ($thirtyDaysAgo) {
                $q->where('created_at', '>=', $thirtyDaysAgo)
                  ->where('status', '!=', 'annulee');
            })
            ->sum('quantity');

        $dailyVelocity = $totalSoldInMonth / 30; // Nombre moyen vendu par jour
        $currentStock = $product->quantity;
        
        if ($dailyVelocity > 0) {
            $daysRemaining = round($currentStock / $dailyVelocity, 1);
        } else {
            $daysRemaining = 999; // Stock stable ou pas de vente
        }

        $heuristicResult = [
            'days_remaining' => $daysRemaining,
            'daily_velocity' => round($dailyVelocity, 2),
            'confidence' => $totalSoldInMonth > 10 ? 'High' : ($totalSoldInMonth > 0 ? 'Medium' : 'Low'),
            'explanation' => "Basé sur la vente de {$totalSoldInMonth} unités au cours des 30 derniers jours (vélocité moyenne de " . round($dailyVelocity, 2) . " unités/jour).",
            'source' => 'Heuristique Locale'
        ];

        // 2. Si un fournisseur d'IA est configuré, on peut enrichir la prédiction
        if ($this->provider === 'openai' && $this->apiKey) {
            return $this->queryOpenAi("Prédire l'alerte stock pour le produit {$product->name} avec un stock de {$currentStock} et des ventes mensuelles de {$totalSoldInMonth}.", $heuristicResult);
        }

        if ($this->provider === 'gemini' && $this->apiKey) {
            return $this->queryGemini("Prédire l'alerte stock pour le produit {$product->name} avec un stock de {$currentStock} et des ventes mensuelles de {$totalSoldInMonth}.", $heuristicResult);
        }

        return $heuristicResult;
    }

    /**
     * Evaluer le score de risque de credit pour un client.
     */
    public function evaluateClientCreditRisk(Client $client): array
    {
        // 1. Calculer le comportement de crédit localement (heuristique)
        $credits = CreditSale::where('client_id', $client->id)->get();
        
        $totalCredits = $credits->count();
        $totalDue = $credits->whereIn('status', ['en_attente', 'partiel', 'en_retard'])->sum('amount_due');
        $overdueCredits = $credits->where('status', 'en_retard')->count();

        // Calculer le taux de remboursement
        $totalBorrowed = $credits->sum('total_amount');
        $totalPaid = $credits->sum('amount_paid');
        $repaymentRate = $totalBorrowed > 0 ? ($totalPaid / $totalBorrowed) * 100 : 100;

        // Etablir le niveau de risque de base
        $riskScore = 'A'; // Très fiable
        $explanation = 'Client très fiable avec un historique impeccable.';

        if ($overdueCredits > 0 || $totalDue > 1000000) {
            $riskScore = 'D'; // Risque élevé
            $explanation = 'Retards de paiement récurrents ou dette importante en cours.';
        } elseif ($totalDue > 500000 || $repaymentRate < 70) {
            $riskScore = 'C'; // Risque modéré
            $explanation = 'Historique correct mais détient une dette active moyenne.';
        } elseif ($totalDue > 0 || $repaymentRate < 90) {
            $riskScore = 'B'; // Risque faible
            $explanation = 'Quelques crédits actifs mineurs, remboursés régulièrement.';
        }

        $heuristicResult = [
            'risk_rating' => $riskScore,
            'repayment_rate' => round($repaymentRate, 1),
            'total_due' => $totalDue,
            'total_credits' => $totalCredits,
            'explanation' => $explanation,
            'source' => 'Heuristique Locale'
        ];

        // Mettre à jour en DB directement pour garder les filtres synchronisés
        $client->update(['risk_rating' => $riskScore]);

        // 2. IA enrichie si disponible
        if ($this->provider === 'openai' && $this->apiKey) {
            return $this->queryOpenAi("Évaluer le score de crédit pour {$client->name} avec un taux de remboursement de {$repaymentRate}% et une dette active de {$totalDue} FCFA.", $heuristicResult);
        }

        if ($this->provider === 'gemini' && $this->apiKey) {
            return $this->queryGemini("Évaluer le score de crédit pour {$client->name} avec un taux de remboursement de {$repaymentRate}% et une dette active de {$totalDue} FCFA.", $heuristicResult);
        }

        return $heuristicResult;
    }

    /**
     * Recommander des produits à un client.
     */
    public function recommendProductsForClient(Client $client): array
    {
        // 1. Algorithme de filtrage collaboratif / par contenu basique en local
        // Trouver les catégories les plus achetées par ce client
        $preferredCategories = DB::table('sale_details')
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->where('sales.client_id', $client->id)
            ->where('sales.status', '!=', 'annulee')
            ->select('products.category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category_id')
            ->orderByDesc('count')
            ->limit(3)
            ->pluck('category_id');

        // Recommander des produits populaires de ces catégories que le client n'a pas achetés,
        // ou qu'il achète souvent et qui ont du stock.
        $recommendedProducts = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->whereIn('category_id', $preferredCategories)
            ->limit(5)
            ->get();

        $heuristicResult = [
            'recommendations' => $recommendedProducts->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'reference' => $p->reference,
                'price' => $p->price_sell,
            ])->toArray(),
            'explanation' => "Recommandé sur la base de vos catégories préférées.",
            'source' => 'Heuristique Locale'
        ];

        if ($this->provider === 'openai' && $this->apiKey) {
            return $this->queryOpenAi("Recommander 3 produits pour le client {$client->name} qui achète principalement dans les catégories ID: " . implode(',', $preferredCategories->toArray()), $heuristicResult);
        }

        if ($this->provider === 'gemini' && $this->apiKey) {
            return $this->queryGemini("Recommander 3 produits pour le client {$client->name} qui achète principalement dans les catégories ID: " . implode(',', $preferredCategories->toArray()), $heuristicResult);
        }

        return $heuristicResult;
    }

    // === METHODES COMPATIBILITE LLM API ===
    
    protected function queryOpenAi(string $prompt, array $fallback): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model ?? 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es l\'assistant IA de KamerStock ERP. Réponds en JSON.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiContent = json_decode($data['choices'][0]['message']['content'], true);
                return array_merge($fallback, $aiContent, ['source' => 'OpenAI GPT API']);
            }
        } catch (\Throwable $e) {
            Log::warning("Erreur OpenAI API, utilisation du fallback local : " . $e->getMessage());
        }

        return $fallback;
    }

    protected function queryGemini(string $prompt, array $fallback): array
    {
        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/" . ($this->model ?? 'gemini-1.5-flash') . ":generateContent?key=" . $this->apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt . " Répond impérativement sous forme de JSON."]]]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Nettoyer les balises de code Markdown JSON si présentes
                $text = preg_replace('/```json|```/', '', $text);
                $aiContent = json_decode(trim($text), true);
                if (is_array($aiContent)) {
                    return array_merge($fallback, $aiContent, ['source' => 'Gemini API']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Erreur Gemini API, utilisation du fallback local : " . $e->getMessage());
        }

        return $fallback;
    }
}
