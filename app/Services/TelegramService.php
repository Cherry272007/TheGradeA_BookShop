<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    /**
     * Send a plain text message to Telegram
     */
    public static function sendMessage(string $message)
    {
        Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Send order details to Telegram when order is created
     */
    public static function sendOrder($order)
    {
        // Force a fresh load of the relationships
        $order->loadMissing(['items.book', 'user']);

        // 🛑 STOP: If there are no items, don't send the "New Order" message yet.
        // This prevents the empty message triggered by Observers/Hooks.
        if ($order->items->isEmpty()) {
            return; 
        }

        $itemsText = "";
        foreach ($order->items as $item) {
            $title = e($item->book->title ?? 'N/A');
            $itemsText .= "• <b>{$title}</b>\n"
                . "   Qty: {$item->quantity}\n"
                . "   Price: \$" . number_format($item->price, 2) . "\n\n";
        }

        $message = "<b>🛒 New Order Received!</b>\n\n"
            . "<b>Order Number:</b> {$order->order_number}\n"
            . "<b>User:</b> {$order->user->name} ({$order->user->email})\n"
            . "<b>Phone:</b> {$order->phone}\n"
            . "<b>Address:</b> {$order->address}\n\n"
            . "<b>Items:</b>\n{$itemsText}"
            . "<b>Subtotal:</b> \$" . number_format($order->subtotal, 2) . "\n"
            . "<b>Shipping Fee:</b> \$" . number_format($order->shipping_fee, 2) . "\n"
            . "<b>Total:</b> \$" . number_format($order->total_amount, 2) . "\n\n"
            . "<b>Payment Method:</b> " . strtoupper($order->payment_method) . "\n"
            . "<b>Order Date:</b> {$order->order_date->format('Y-m-d H:i')}\n";

        self::sendMessage($message);
    }

    /**
     * Send Telegram message when order status changes
     */
    public static function sendStatusUpdate($order)
    {
        $order->loadMissing('user');

        $message = "<b>🔄 Order Status Updated!</b>\n\n"
            . "<b>Order Number:</b> {$order->order_number}\n"
            . "<b>User:</b> {$order->user->name} ({$order->user->email})\n"
            . "<b>New Status:</b> {$order->status}\n"
            . "<b>Payment Status:</b> {$order->payment_status}\n"
            . "<b>Updated At:</b> {$order->updated_at->format('Y-m-d H:i')}\n";

        self::sendMessage($message);
    }
}