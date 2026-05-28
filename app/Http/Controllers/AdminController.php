<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Banner;
use App\Models\Slider;
use App\Models\Faq;
use App\Services\DigiflazzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $digiflazz;

    public function __construct(DigiflazzService $digiflazz)
    {
        $this->digiflazz = $digiflazz;
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function manualTopup(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::find($validated['user_id']);
            $oldBalance = $user->balance;
            
            $user->balance += $validated['amount'];
            $user->save();

            $user->transactions()->create([
                'type' => 'topup',
                'amount' => $validated['amount'],
                'balance_before' => $oldBalance,
                'balance_after' => $user->balance,
                'description' => $validated['description'] ?? 'Manual topup by admin'
            ]);
        });

        return response()->json(['message' => 'Topup successful']);
    }

    public function depositToVendor(Request $request)
    {
        $validated = $request->validate([
            'vendor' => 'required|in:digiflazz',
            'amount' => 'required|numeric|min:10000'
        ]);

        if ($validated['vendor'] === 'digiflazz') {
            $response = $this->digiflazz->deposit($validated['amount']);
            
            if ($response['success']) {
                return response()->json(['message' => 'Deposit successful']);
            } else {
                return response()->json(['message' => 'Deposit failed'], 400);
            }
        }
    }

    public function manageSettings(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'nullable'
        ]);

        Setting::updateOrCreate(
            ['key' => $validated['key']],
            ['value' => $validated['value']]
        );

        return response()->json(['message' => 'Setting saved']);
    }

    public function manageBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'image' => 'required|string',
            'link' => 'nullable|url',
            'order' => 'integer'
        ]);

        $banner = Banner::create($validated);
        return response()->json($banner);
    }

    public function manageSlider(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'image' => 'required|string',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|url',
            'order' => 'integer'
        ]);

        $slider = Slider::create($validated);
        return response()->json($slider);
    }

    public function manageFaq(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'order' => 'integer'
        ]);

        $faq = Faq::create($validated);
        return response()->json($faq);
    }

    public function updateSEO(Request $request)
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'google_tag_manager' => 'nullable|string',
            'google_adsense' => 'nullable|string',
            'facebook_pixel' => 'nullable|string'
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => "seo_$key"],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'SEO settings updated']);
    }

    public function getReports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $reports = [
            'total_sales' => Order::where('status', 'success')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->sum('amount'),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'top_products' => Product::withCount(['orders' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'success')
                  ->whereBetween('completed_at', [$startDate, $endDate]);
            }])->orderBy('orders_count', 'desc')->limit(10)->get(),
            'daily_sales' => Order::where('status', 'success')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->select(DB::raw('DATE(completed_at) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->get()
        ];

        return response()->json($reports);
    }
}