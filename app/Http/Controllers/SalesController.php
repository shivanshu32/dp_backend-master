<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    //
    public function GetOrdersList(Request $request) {
        $orders = Order::whereCreatedBy(auth()->user()->id)->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $orders
        ]);
    }

    
}
