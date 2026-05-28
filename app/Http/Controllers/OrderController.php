<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\DigiflazzService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $digiflazz;
    protected $midtrans;

    public function __construct(DigiflazzService $digiflazz, MidtransService $midtrans)
    {
        $this->digiflazz = $digiflazz;
        $this->midtrans = $midtrans;
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'payment_method' => 'required|in:balance,midtrans'
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $user = auth()->user();

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . time() . rand(1000, 9999),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'amount' => $product->sell_price,
                'status' => 'pending',
                'payment_method' => $validated['payment_method']
            ]);

            // Process payment
            if ($validated['payment_method'] === 'balance') {
                if ($user->balance < $product->sell_price) {
                    throw new \Exception('Insufficient balance');
                }

                // Deduct balance
                $user->balance -= $product->sell_price;
                $user->save();

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'type' => 'payment',
                    'amount' => $product->sell_price,
                    'balance_before' => $user->balance + $product->sell_price,
                    'balance_after' => $user->balance,
                    'description' => "Payment for order {$order->order_number}"
                ]);

                $order->update(['status' => 'processing']);
                
                // Process order fulfillment
                $this->processOrder($order);

                DB::commit();

                return response()->json([
                    'order' => $order,
                    'status' => 'success'
                ]);
            } 
            else if ($validated['payment_method'] === 'midtrans') {
                // Create Midtrans payment
                $paymentUrl = $this->midtrans->createPayment($order);
                
                DB::commit();

                return response()->json([
                    'order' => $order,
                    'payment_url' => $paymentUrl
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function processOrder(Order $order)
    {
        try {
            $product = $order->product;
            
            // Call Digiflazz API
            $response = $this->digiflazz->topup([
                'product_id' => $product->vendor_product_id,
                'customer_phone' => $order->customer_phone,
                'order_id' => $order->order_number
            ]);

            if ($response['success']) {
                $order->update([
                    'status' => 'success',
                    'vendor_order_id' => $response['vendor_order_id'],
                    'vendor_response' => $response,
                    'completed_at' => now()
                ]);

                // Send notifications
                $this->sendOrderSuccessNotification($order);
            } else {
                throw new \Exception($response['message'] ?? 'Vendor processing failed');
            }
        } catch (\Exception $e) {
            // Retry logic
            if ($order->retry_count < 3) {
                $order->increment('retry_count');
                dispatch(new \App\Jobs\RetryOrderJob($order))->delay(now()->addMinutes(5));
            } else {
                $order->update(['status' => 'failed']);
                
                // Auto refund
                $this->refundOrder($order);
            }
        }
    }

    public function refundOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            $user = $order->user;
            
            // Refund balance
            $user->balance += $order->amount;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'type' => 'refund',
                'amount' => $order->amount,
                'balance_before' => $user->balance - $order->amount,
                'balance_after' => $user->balance,
                'description' => "Refund for failed order {$order->order_number}"
            ]);

            $order->update(['status' => 'refunded']);
            
            // Send refund notification
            $this->sendRefundNotification($order);
        });
    }

    public function history(Request $request)
    {
        $orders = Order::with('product')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    public function track($orderNumber)
    {
        $order = Order::with('product')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json($order);
    }

    private function sendOrderSuccessNotification($order)
    {
        // Send via WhatsApp, Telegram, Email
        $message = "✅ Order {$order->order_number} has been completed successfully!\nProduct: {$order->product->name}\nAmount: Rp " . number_format($order->amount);
        
        // Implement notification sending
    }

    private function sendRefundNotification($order)
    {
        $message = "⚠️ Order {$order->order_number} failed. Refund of Rp " . number_format($order->amount) . " has been processed.";
        
        // Implement notification sending
    }
}