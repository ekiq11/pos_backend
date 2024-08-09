<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    //index
    public function index()
    {
        // Ambil ID kasir dari user yang sedang login
        $kasirId = Auth::user()->id;

        // Ambil data order berdasarkan kasir_id yang sedang login
        $orders = \App\Models\Order::with('kasir')
            ->where('kasir_id', $kasirId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.orders.index', compact('orders'));
    }

    //view
    public function show($id)
    {
        $order = \App\Models\Order::with('kasir')->findOrFail($id);

        // Cek apakah order milik kasir yang sedang login
        if ($order->kasir_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get order items by order id
        $orderItems = \App\Models\OrderItem::with('product')->where('order_id', $id)->get();

        return view('pages.orders.view', compact('order', 'orderItems'));
    }
}
