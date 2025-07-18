<?php
namespace App\Services;

use App\Models\Product;
use App\Models\Category;

class SkuService
{

    public function generate(Product $product)
    {
        $categoryPrefix = strtoupper(substr($product->category->name, 0, 3));
        $uniqueId = str_pad($product->id, 4, '0', STR_PAD_LEFT);
        $variantCode = ''; // You could add variant support later

        return "{$categoryPrefix}-{$uniqueId}{$variantCode}";
    }

    public function generateForCategory(Category $category, $productId)
    {
        $categoryPrefix = strtoupper(substr($category->name, 0, 3));
        $uniqueId = str_pad($productId, 4, '0', STR_PAD_LEFT);

        return "{$categoryPrefix}-{$uniqueId}";
    }
}
