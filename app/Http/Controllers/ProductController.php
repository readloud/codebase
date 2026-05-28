<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\DigiflazzService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $digiflazz;

    public function __construct(DigiflazzService $digiflazz)
    {
        $this->digiflazz = $digiflazz;
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand'])->where('is_active', true);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'brand'])->findOrFail($id);
        return response()->json($product);
    }

    public function categories()
    {
        $categories = Category::orderBy('order')->get();
        return response()->json($categories);
    }

    public function brands()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    public function syncFromDigiflazz()
    {
        // Check permission
        if (!auth()->user()->hasPermission('sync_products')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $products = $this->digiflazz->getProducts();
        
        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'vendor_product_id' => $productData['product_id'],
                    'vendor' => 'digiflazz',
                    'buy_price' => $productData['buy_price'],
                    'sell_price' => $productData['sell_price'],
                    'category_id' => $this->getOrCreateCategory($productData['category']),
                    'brand_id' => $this->getOrCreateBrand($productData['brand']),
                    'metadata' => $productData
                ]
            );
        }

        return response()->json(['message' => 'Products synced successfully']);
    }

    private function getOrCreateCategory($name)
    {
        if (!$name) return null;
        
        $category = Category::firstOrCreate(
            ['slug' => \Str::slug($name)],
            ['name' => $name]
        );
        
        return $category->id;
    }

    private function getOrCreateBrand($name)
    {
        if (!$name) return null;
        
        $brand = Brand::firstOrCreate(
            ['slug' => \Str::slug($name)],
            ['name' => $name]
        );
        
        return $brand->id;
    }
}