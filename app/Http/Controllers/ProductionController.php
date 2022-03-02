<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function GetProductionOrders(Request $request) {
        $orders = Order::whereStatus('Production')->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $orders
        ]);
    }
}
