<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TempProduct;

class ProductMatchingService
{
    /**
     * Find matching products for a temp product.
     * Returns top 3 matches with confidence scores.
     *
     * @param TempProduct $tempProduct
     * @param string $businessId
     * @return array
     */
    public function findMatches(TempProduct $tempProduct, string $businessId): array
    {
        $matches = [];

        // Get all products for this business
        $products = Product::where('business_id', $businessId)->get();

        foreach ($products as $product) {
            $confidence = $this->calculateConfidence($tempProduct, $product);

            if ($confidence >= 0.60) { // Only include matches with 60%+ confidence
                $matches[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->stock_quantity ?? 0,
                    'selling_price' => $product->selling_price,
                    'confidence' => $confidence,
                ];
            }
        }

        // Sort by confidence (highest first)
        usort($matches, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        // Return top 3 matches
        return array_slice($matches, 0, 3);
    }

    /**
     * Calculate confidence score for matching.
     *
     * @param TempProduct $tempProduct
     * @param Product $product
     * @return float
     */
    protected function calculateConfidence(TempProduct $tempProduct, Product $product): float
    {
        $score = 0.0;

        // 1. Exact SKU match = 100% confidence
        if (
            $tempProduct->sku && $product->sku &&
            strtolower($tempProduct->sku) === strtolower($product->sku)
        ) {
            return 1.00;
        }

        // 2. Exact name match = 95% confidence
        if (strtolower($tempProduct->name) === strtolower($product->name)) {
            return 0.95;
        }

        // 3. Fuzzy name matching using Levenshtein distance
        $nameSimilarity = $this->calculateSimilarity(
            strtolower($tempProduct->name),
            strtolower($product->name)
        );
        $score += $nameSimilarity * 0.70; // Name contributes 70% max

        // 4. HSN code match
        if (
            $tempProduct->hsn_code && $product->hsn_code &&
            $tempProduct->hsn_code === $product->hsn_code
        ) {
            $score += 0.20; // HSN match adds 20%
        }

        // 5. SKU partial match
        if ($tempProduct->sku && $product->sku) {
            $skuSimilarity = $this->calculateSimilarity(
                strtolower($tempProduct->sku),
                strtolower($product->sku)
            );
            $score += $skuSimilarity * 0.10; // SKU contributes 10% max
        }

        return min($score, 0.99); // Cap at 99% (only exact SKU gets 100%)
    }

    /**
     * Calculate string similarity using Levenshtein distance.
     *
     * @param string $str1
     * @param string $str2
     * @return float
     */
    protected function calculateSimilarity(string $str1, string $str2): float
    {
        $maxLen = max(strlen($str1), strlen($str2));

        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);
        $similarity = 1 - ($distance / $maxLen);

        return max(0.0, $similarity);
    }
}
