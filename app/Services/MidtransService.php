<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\Order;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createPayment(Order $order)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->amount
            ],
            'customer_details' => [
                'first_name' => $order->user->username,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone
            ],
            'item_details' => [
                [
                    'id' => $order->product->sku,
                    'price' => (int) $order->product->sell_price,
                    'quantity' => 1,
                    'name' => $order->product->name
                ]
            ]
        ];

        return Snap::createTransaction($params)->redirect_url;
    }

    public function handleWebhook($payload)
    {
        $notification = new Notification();
        
        $orderNumber = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return false;
        }

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $order->update(['status' => 'processing']);
                dispatch(new \App\Jobs\ProcessOrderJob($order));
            }
        } else if ($transactionStatus == 'settlement') {
            $order->update(['status' => 'processing']);
            dispatch(new \App\Jobs\ProcessOrderJob($order));
        } else if ($transactionStatus == 'pending') {
            $order->update(['status' => 'pending']);
        } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $order->update(['status' => 'failed']);
        }

        return true;
    }
}