<?php

namespace App\Services;

use App\Models\Setting;

class BusinessTypeService
{
    /**
     * Get the current business type.
     */
    public function current(): string
    {
        try {
            $setting = Setting::first();
            return $setting?->business_type ?? 'quincaillerie';
        } catch (\Exception $e) {
            return 'quincaillerie';
        }
    }

    /**
     * Get the custom business type name.
     */
    public function customName(): ?string
    {
        try {
            $setting = Setting::first();
            return $setting?->business_type_custom;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the configuration array for the current business type.
     */
    protected function getConfig(): array
    {
        $type = $this->current();
        $config = config("business_types.{$type}");
        
        if (!$config) {
            $config = config("business_types.quincaillerie") ?? [
                'label' => 'quincailleriee',
                'subtitle' => 'Matériaux, outillage et articles de construction',
                'product_label' => 'Article',
                'product_plural_label' => 'Articles',
                'category_label' => 'Famille d’articles',
                'supplier_label' => 'Fournisseur',
                'default_units' => 'pièce, mètre, kg, litre, boîte, sachet',
                'default_categories' => ['Ciment', 'Fer', 'Outillage', 'Plomberie', 'Électricité', 'Peinture'],
                'dashboard_title' => 'Gestion de quincaillerie',
            ];
        }

        return $config;
    }

    /**
     * Get the business label.
     */
    public function label(): string
    {
        if ($this->current() === 'autre') {
            return $this->customName() ?: ($this->getConfig()['label'] ?? 'Commerce');
        }
        return $this->getConfig()['label'] ?? 'Commerce';
    }

    /**
     * Get the business subtitle.
     */
    public function subtitle(): string
    {
        if ($this->current() === 'autre' && $this->customName()) {
            return $this->customName();
        }
        return $this->getConfig()['label'] ?? 'kamerstock';
    }

    /**
     * Get the product singular label.
     */
    public function productLabel(): string
    {
        return $this->getConfig()['product_label'] ?? 'Produit';
    }

    /**
     * Get the product plural label.
     */
    public function productPluralLabel(): string
    {
        return $this->getConfig()['product_plural_label'] ?? 'Produits';
    }

    /**
     * Get the category label.
     */
    public function categoryLabel(): string
    {
        return $this->getConfig()['category_label'] ?? 'Catégorie';
    }

    /**
     * Get the supplier label.
     */
    public function supplierLabel(): string
    {
        return $this->getConfig()['supplier_label'] ?? 'Fournisseur';
    }

    /**
     * Get the default units string.
     */
    public function defaultUnits(): string
    {
        return $this->getConfig()['default_units'] ?? 'pièce, kg, litre, boîte, sachet';
    }

    /**
     * Get default categories array.
     */
    public function defaultCategories(): array
    {
        $categories = $this->getConfig()['default_categories'] ?? ['Général'];
        if (is_array($categories)) {
            return $categories;
        }
        return array_map('trim', explode(',', $categories));
    }

    /**
     * Get dashboard title.
     */
    public function dashboardTitle(): string
    {
        if ($this->current() === 'autre') {
            return $this->customName() ? "Gestion de {$this->customName()}" : "Gestion de stock, vente et caisse";
        }
        return $this->getConfig()['dashboard_title'] ?? 'Gestion de stock, vente et caisse';
    }

    /**
     * Get proposed units mapped to keys.
     */
    public function proposedUnits(): array
    {
        $all = [
            'piece'   => 'Pièce(s)',
            'metre'   => 'Mètre(s)',
            'kg'      => 'Kg',
            'litre'   => 'Litre(s)',
            'boite'   => 'Boîte(s)',
            'sachet'  => 'Sachet(s)',
            'carton'  => 'Carton(s)',
            'paquet'  => 'Paquet(s)',
            'flacon'  => 'Flacon(s)',
            'tube'    => 'Tube(s)',
            'kit'     => 'Kit(s)',
            'lot'     => 'Lot(s)',
            'palette' => 'Palette(s)',
            'sac'     => 'Sac(s)',
        ];

        try {
            $setting = Setting::first();
            if ($setting && is_array($setting->enabled_units) && count($setting->enabled_units) > 0) {
                $enabled = [];
                foreach ($setting->enabled_units as $unitKey) {
                    if (isset($all[$unitKey])) {
                        $enabled[$unitKey] = $all[$unitKey];
                    }
                }
                if (count($enabled) > 0) {
                    return $enabled;
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        $unitsStr = $this->defaultUnits();
        $unitsArr = array_map('trim', explode(',', $unitsStr));
        
        $map = [
            'pièce'   => 'piece',
            'mètre'   => 'metre',
            'kg'      => 'kg',
            'litre'   => 'litre',
            'boîte'   => 'boite',
            'sachet'  => 'sachet',
            'carton'  => 'carton',
            'paquet'  => 'paquet',
            'flacon'  => 'flacon',
            'tube'    => 'tube',
            'kit'     => 'kit',
            'lot'     => 'lot',
            'palette' => 'palette',
            'sac'     => 'sac',
        ];
        
        $proposed = [];
        foreach ($unitsArr as $u) {
            $lowerU = mb_strtolower($u);
            if (isset($map[$lowerU])) {
                $code = $map[$lowerU];
                if (isset($all[$code])) {
                    $proposed[$code] = $all[$code];
                }
            }
        }
        
        // Base fallback units to ensure no breaking changes
        $base = [
            'piece'  => 'Pièce(s)',
            'metre'  => 'Mètre(s)',
            'kg'     => 'Kg',
            'litre'  => 'Litre(s)',
            'boite'  => 'Boîte(s)',
            'sachet' => 'Sachet(s)',
        ];
        
        return array_merge($proposed, $base);
    }
}
