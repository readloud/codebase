<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected $telegramBotToken;
    protected $telegramChatId;
    protected $whatsappApiUrl;

    public function __construct()
    {
        $this->telegramBotToken = config('services.telegram.bot_token');
        $this->telegramChatId = config('services.telegram.chat_id');
        $this->whatsappApiUrl = config('services.whatsapp.api_url');
    }

    public function sendWhatsApp($to, $message)
    {
        // Implement with WhatsApp Business API or third-party like WATI
        $response = Http::withHeaders([
            'Authorization' => config('services.whatsapp.api_key')
        ])->post($this->whatsappApiUrl . '/messages', [
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message]
        ]);

        return $response->successful();
    }

    public function sendTelegram($message)
    {
        $url = "https://api.telegram.org/bot{$this->telegramBotToken}/sendMessage";
        
        $response = Http::post($url, [
            'chat_id' => $this->telegramChatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);

        return $response->successful();
    }

    public function sendEmail($to, $subject, $content)
    {
        Mail::send([], [], function ($message) use ($to, $subject, $content) {
            $message->to($to)
                    ->subject($subject)
                    ->setBody($content, 'text/html');
        });

        return true;
    }

    public function sendOrderNotification($order)
    {
        $message = "🎉 New Order!\nOrder ID: {$order->order_number}\nProduct: {$order->product->name}\nAmount: Rp " . number_format($order->amount);
        
        $this->sendTelegram($message);
        
        // Send to admin WhatsApp
        $adminPhones = User::where('role_id', 1)->pluck('phone');
        foreach ($adminPhones as $phone) {
            $this->sendWhatsApp($phone, $message);
        }
    }
}