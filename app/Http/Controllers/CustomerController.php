<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function GetProductionOrder(Request $request) {
        $orders = Order::whereCustomerId(auth()->user()->id)->where('status', '!=', 'Completed')->orderBy('id', 'DESC')->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $orders
        ]);
    }

    public function GetCompletedOrder(Request $request) {
        $orders = Order::whereCustomerId(auth()->user()->id)->whereStatus('Completed')->orderBy('id', 'DESC')->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $orders
        ]);
    }

    public function DeleteCustomerOrder(Request $request, $orderID) {
        $order = Order::whereCustomerId(auth()->user()->id)->whereId($orderID)->first();
        if($order) {
            if($order->status ==  'Processing') {
                if($order->delete()) {
                    return response([
                        'code' => 200,
                        'success' => false,
                        'message' => 'Order successfully deleted.'
                    ]);
                } else {
                    return response([
                        'code' => 500,
                        'success' => false,
                        'message' => 'Something went wrong. Try again later.'
                    ]);
                }
            } else {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => 'Order already is in production. You can\'t delete once its in production.'
                ]);
            }
        } else {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Order not found.'
            ]);
        }
       
    } 
}
