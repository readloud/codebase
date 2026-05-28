<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSyncLog;
use App\Services\DigiflazzService;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DigiflazzService $digiflazz)
    {
        $products = $digiflazz->getProducts();
        $synced = 0;
        $errors = [];

        foreach ($products as $productData) {
            try {
                Product::updateOrCreate(
                    ['sku' => $productData['sku']],
                    [
                        'name' => $productData['name'],
                        'vendor_product_id' => $productData['product_id'],
                        'vendor' => 'digiflazz',
                        'buy_price' => $productData['buy_price'],
                        'sell_price' => $productData['sell_price'],
                        'metadata' => $productData
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = $productData['sku'] . ': ' . $e->getMessage();
            }
        }

        ProductSyncLog::create([
            'products_synced' => $synced,
            'errors' => $errors,
            'synced_at' => now()
        ]);
    }
}