<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DigiflazzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(DigiflazzService $digiflazz)
    {
        $response = $digiflazz->topup([
            'product_id' => $this->order->product->vendor_product_id,
            'customer_phone' => $this->order->customer_phone,
            'order_id' => $this->order->order_number
        ]);

        if ($response['success']) {
            $this->order->update([
                'status' => 'success',
                'vendor_order_id' => $response['vendor_order_id'],
                'completed_at' => now()
            ]);
        } else {
            if ($this->attempts() < 3) {
                $this->release(300); // Retry after 5 minutes
                $this->order->increment('retry_count');
            } else {
                $this->order->update(['status' => 'failed']);
                // Trigger refund
                dispatch(new RefundOrderJob($this->order));
            }
        }
    }
}