<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\TelegramService; // ✅ ADD THIS

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.book')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address'        => 'required|string',
            'phone'          => 'required|string',
            'payment_method' => 'required|in:cod,stripe,paypal',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:books,id',
            'items.*.qty'    => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {

                $subtotal = 0;
                $orderItemsData = [];

                // 1. Validate stock & prepare items
                foreach ($request->items as $item) {
                    $book = Book::lockForUpdate()->findOrFail($item['id']);

                    if ($book->stock < $item['qty']) {
                        throw new \Exception("Book '{$book->title}' is out of stock.");
                    }

                    $itemPrice = $book->price;
                    $subtotal += ($itemPrice * $item['qty']);

                    $orderItemsData[] = [
                        'book_id'  => $book->id,
                        'quantity' => $item['qty'],
                        'price'    => $itemPrice,
                        // subtotal auto-calculated in model
                    ];

                    // Reduce stock
                    $book->decrement('stock', $item['qty']);
                }

                // 2. Create order
                $shippingFee = 1.38;

                $order = Order::create([
                    'user_id'        => $request->user()->id,
                    'order_number'   => 'ORD-' . strtoupper(Str::random(10)),
                    'subtotal'       => $subtotal,
                    'shipping_fee'   => $shippingFee,
                    'total_amount'   => $subtotal + $shippingFee,
                    'status'         => 'pending',
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
                    'address'        => $request->address,
                    'phone'          => $request->phone,
                    'notes'          => $request->notes,
                    'order_date'     => now(),
                ]);

                // 3. Create order items
                $order->items()->createMany($orderItemsData);

                // ✅ 4. LOAD RELATIONSHIPS (VERY IMPORTANT)
                $order->load('items.book', 'user');

                // ✅ 5. SEND TELEGRAM (AFTER EVERYTHING IS READY)
                TelegramService::sendOrder($order);

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'order'   => $order
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified order
     */
    public function show($id, Request $request)
    {
        $order = Order::with('items.book')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Admin: View all orders
     */
    public function adminIndex()
    {
        $orders = Order::with(['user', 'items.book']) // ✅ FIXED
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Admin: Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'         => 'sometimes|in:pending,processing,shipped,completed,cancelled',
            'payment_status' => 'sometimes|in:paid,unpaid',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::findOrFail($id);
        $order->update($request->only(['status', 'payment_status']));

        // ✅ OPTIONAL: send update to Telegram
        TelegramService::sendStatusUpdate($order->load('user'));

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data'    => $order
        ]);
    }
}