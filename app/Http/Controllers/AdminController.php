<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Misc;
use App\Models\Printer;
use Validator;
use App\Models\Order;
use App\Models\Resource;
use App\Models\OrderArt;
use App\Mail\OrderProduction;
use App\Mail\OrderCompleted;
use App\Mail\ResourceEmail;
use Mail;
use Twilio;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Get Users List
    public function GetUsersList(Request $request) {
        $users = User::select(DB::Raw('*'));
        if(auth()->user()->role == 'Sales') {
            $users = User::where('created_by', auth()->user()->id);
        }

        if($request->query !== null){
            $users = $users->where(function($query) use($request){
                $query->orWhere('first_name','like','%'.$request->get('query').'%')
                ->orWhere('last_name','like','%'.$request->get('query').'%')
                ->orWhere('email','like','%'.$request->get('query').'%');
            });
        }

        $dataToReturn = $users->paginate(6)->toArray();
        $dataToReturn['code'] = 200;
        $dataToReturn['success'] = true;
        $dataToReturn['message'] = 'Customer List.';

        return response()->json($dataToReturn);
    }


    // Create User
    public function CreateUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|max:150|unique:users,email',
            'role' => 'required|in:Admin,Production,Sales,Customer,Printer',
            'password' => 'required',
            'contact_number' => 'max:100',
            'address' => 'max:1000',
            'street_appartment' => 'max:50',
            'city' => 'max:50',
            'state' => 'max:50',
            'zipcode' => 'max:50',
            'company' => 'max:50',

            'shipping_name' => 'max:50',
            'shipping_last_name' => 'max:50',
            'shipping_company' => 'max:50',
            'shipping_address' => 'max:1000',
            'shipping_street_appartment' => 'max:50',
            'shipping_phone' => 'max:100',
            'shipping_city' => 'max:50',
            'shipping_state' => 'max:50',
            'shipping_zipcode' => 'max:50',
            'shipping_email' => 'max:100|email|nullable',
        ]);



        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        if(auth()->user()->role == 'Sales' && $request->role != 'Customer') {
            return response([
                'code' => 400,
                'success' => false,
                'message' => 'Only add customer type account'
            ]);
        }

        $user = new User;
        $user->role = $request->role;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->company = $request->company;
        $user->street_appartment = $request->street_appartment;
        $user->created_by = auth()->user()->id;
        $user->email = $request->email;
        $user->status = 1;

        if(isset($request->contact_number) && !empty($request->contact_number))
            $user->contact_number = $request->contact_number;
        else
            $user->contact_number = '';

        if(isset($request->address) && !empty($request->address))
            $user->address = $request->address;
        else
            $user->address = '';

        if(isset($request->city) && !empty($request->city))
            $user->city = $request->city;
        else
            $user->city = '';

        if(isset($request->state) && !empty($request->state))
            $user->state = $request->state;
        else
            $user->state = '';

        if(isset($request->zipcode) && !empty($request->zipcode))
            $user->zipcode = $request->zipcode;
        else
            $user->zipcode = '';


        if(isset($request->shipping_name) && !empty($request->shipping_name))
            $user->shipping_name = $request->shipping_name;
        else
            $user->shipping_name = '';

        if(isset($request->shipping_last_name) && !empty($request->shipping_last_name))
            $user->shipping_last_name = $request->shipping_last_name;
        else
            $user->shipping_last_name = '';

        if(isset($request->shipping_company) && !empty($request->shipping_company))
            $user->shipping_company = $request->shipping_company;
        else
            $user->shipping_company = '';

        if(isset($request->shipping_address) && !empty($request->shipping_address))
            $user->shipping_address = $request->shipping_address;
        else
            $user->shipping_address = '';

        if(isset($request->shipping_street_appartment) && !empty($request->shipping_street_appartment))
            $user->shipping_street_appartment = $request->shipping_street_appartment;
        else
            $user->shipping_street_appartment = '';

        if(isset($request->shipping_phone) && !empty($request->shipping_phone))
            $user->shipping_phone = $request->shipping_phone;
        else
            $user->shipping_phone = '';

        if(isset($request->shipping_city) && !empty($request->shipping_city))
            $user->shipping_city = $request->shipping_city;
        else
            $user->shipping_city = '';

        if(isset($request->shipping_state) && !empty($request->shipping_state))
            $user->shipping_state = $request->shipping_state;
        else
            $user->shipping_state = '';

        if(isset($request->shipping_zipcode) && !empty($request->shipping_zipcode))
            $user->shipping_zipcode = $request->shipping_zipcode;
        else
            $user->shipping_zipcode = '';

        if(isset($request->shipping_email) && !empty($request->shipping_email))
            $user->shipping_email = $request->shipping_email;
        else
            $user->shipping_email = '';


        if($user->save()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'User successfully created.',
                'data' => $user
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

    // Create User
    public function UserUpdate(Request $request, $userId) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|max:150|unique:users,email,'.$userId,
            'role' => 'required|in:Admin,Production,Sales,Customer,Printer',
            'password' => 'required',
            'contact_number' => 'max:100',
            'address' => 'max:1000',
            'street_appartment' => 'max:50',
            'city' => 'max:50',
            'state' => 'max:50',
            'zipcode' => 'max:50',
            'company' => 'max:50',

            'shipping_name' => 'max:50',
            'shipping_last_name' => 'max:50',
            'shipping_company' => 'max:50',
            'shipping_address' => 'max:1000',
            'shipping_street_appartment' => 'max:50',
            'shipping_phone' => 'max:100',
            'shipping_city' => 'max:50',
            'shipping_state' => 'max:50',
            'shipping_zipcode' => 'max:50',
            'shipping_email' => 'max:100|email|nullable',
        ]);


        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = User::find($userId);
        if(auth()->user()->role == 'Sales') {
            $user = User::where('created_by', auth()->user()->id)->where('id',$userId)->first();
        }
        if(!$user) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->company = $request->company;
        $user->street_appartment = $request->street_appartment;

        $user->status = 1;

        if(isset($request->password))
            $user->password = bcrypt($request->password);


        if(isset($request->contact_number) && !empty($request->contact_number))
            $user->contact_number = $request->contact_number;
        else
            $user->contact_number = '';

        if(isset($request->address) && !empty($request->address))
            $user->address = $request->address;
        else
            $user->address = '';

        if(isset($request->city) && !empty($request->city))
            $user->city = $request->city;
        else
            $user->city = '';

        if(isset($request->state) && !empty($request->state))
            $user->state = $request->state;
        else
            $user->state = '';

        if(isset($request->zipcode) && !empty($request->zipcode))
            $user->zipcode = $request->zipcode;
        else
            $user->zipcode = '';

        if(isset($request->shipping_email) && !empty($request->shipping_email))
            $user->shipping_email = $request->shipping_email;
        else
            $user->shipping_email = '';

        if(isset($request->shipping_name) && !empty($request->shipping_name))
            $user->shipping_name = $request->shipping_name;
        else
            $user->shipping_name = '';

        if(isset($request->shipping_last_name) && !empty($request->shipping_last_name))
            $user->shipping_last_name = $request->shipping_last_name;
        else
            $user->shipping_last_name = '';

        if(isset($request->shipping_company) && !empty($request->shipping_company))
            $user->shipping_company = $request->shipping_company;
        else
            $user->shipping_company = '';

        if(isset($request->shipping_address) && !empty($request->shipping_address))
            $user->shipping_address = $request->shipping_address;
        else
            $user->shipping_address = '';

        if(isset($request->shipping_street_appartment) && !empty($request->shipping_street_appartment))
            $user->shipping_street_appartment = $request->shipping_street_appartment;
        else
            $user->shipping_street_appartment = '';

        if(isset($request->shipping_phone) && !empty($request->shipping_phone))
            $user->shipping_phone = $request->shipping_phone;
        else
            $user->shipping_phone = '';

        if(isset($request->shipping_city) && !empty($request->shipping_city))
            $user->shipping_city = $request->shipping_city;
        else
            $user->shipping_city = '';

        if(isset($request->shipping_state) && !empty($request->shipping_state))
            $user->shipping_state = $request->shipping_state;
        else
            $user->shipping_state = '';

        if(isset($request->shipping_zipcode) && !empty($request->shipping_zipcode))
            $user->shipping_zipcode = $request->shipping_zipcode;
        else
            $user->shipping_zipcode = '';


        if($user->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'User successfully updated.',
                'data' => $user
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

    // Delete User
    public function DeleteUser(Request $request, $userId) {
        $user = User::find($userId);
        if(auth()->user()->role == 'Sales') {
            $user = User::where('created_by', auth()->user()->id)->where('id',$userId)->first();
        }
        if($user) {
            $user->deleted_at = date('Y-m-d H:i:s');
            if($user->update()) {
                return response([
                    'code' => 200,
                    'success' => true,
                    'message' => 'Account successfully deleted.'
                ]);
            } else {
                return response([
                    'code' => 500,
                    'success' => false,
                    'message' => 'Something went wrong, Try again later.'
                ]);
            }
        } else {
            return response([
                'code' => 400,
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    // Update User Status
    public function UserStatus(Request $request, $userId) {
        $user = User::find($userId);
        if(auth()->user()->role == 'Sales') {
            $user = User::where('created_by', auth()->user()->id)->where('id',$userId)->first();
        }
        if($user) {
            $user->status = !$user->status;
            if($user->update()) {
                return response([
                    'code' => 200,
                    'success' => true,
                    'message' => 'Account status updated.'
                ]);
            } else {
                return response([
                    'code' => 500,
                    'success' => false,
                    'message' => 'Something went wrong, Try again later.'
                ]);
            }
        } else {
            return response([
                'code' => 400,
                'success' => false,
                'message' => 'User not found.'
            ]);
        }
    }

    // Update order status
    public function UpdateOrderStatus(Request $request, $orderId) {
        $order = Order::find($orderId);
        if(auth()->user()->role == 'Sales') {
            $user = Order::where('created_by', auth()->user()->id)->where('id',$orderId)->first();
        }
        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }

        if(isset($request->art_is_sized))
            $order->art_is_sized = $request->art_is_sized == 'true' ? true: false;

        if(isset($request->pay))
            $order->pay = $request->pay == 'true' ? true: false;

        if(isset($request->apparel))
            $order->apparel = $request->apparel  == 'true' ? true: false;

        if(isset($request->film))
            $order->film = $request->film  == 'true' ? true: false;

        if(isset($request->invoice_number))
            $order->invoice_number = $request->invoice_number;

            if(isset($request->type_notes))
            $order->type_notes = $request->type_notes;

        if(isset($request->po_number))
            $order->po_number = $request->po_number;

        if(isset($request->tracking_number))
            $order->tracking_number = $request->tracking_number;

        if(isset($request->printer_id))
            $order->printer_id = $request->printer_id;

        if(isset($request->s_intro_email))
            $order->s_intro_email = $request->s_intro_email;

        if(isset($request->s_send_proof))
            $order->s_send_proof = $request->s_send_proof;

        if(isset($request->s_proof_approved))
            $order->s_proof_approved = $request->s_proof_approved;

        if(isset($request->s_rush_shipping_paid))
            $order->s_rush_shipping_paid = $request->s_rush_shipping_paid;

        if(isset($request->s_follow_up))
            $order->s_follow_up = $request->s_follow_up;

        if(isset($request->production) && $request->production == 'true') {
            $order->status = 'Production';
            $user = User::find($order->customer_id);
            try {

                if($order->resource_id) {
                    $resource_user = Resource::find($order->resource_id);
                    if($resource_user !== null) {
                        $orderArt = OrderArt::whereOrderId($order->id)->get();
                        try {
                            Mail::to($resource_user->email)->send(new ResourceEmail($order));
                        }catch(\Exception $e){
                            return response([
                                'code' => 500,
                                'success' => false,
                                'message' => "Unable to send email. Error: " . $e->getMessage()
                            ]);
                        }

                    }
                }

                if(!$order->customer_phone) {
                    return response([
                        'code' => 400,
                        'success' => false,
                        'message' => "Kindly enter valid phone number"
                    ]);
                }
                if(isset($request->sms) && $request->sms == 'true') {
                    $arrivalDate = date("D,F jS", strtotime($order->arrival_date));
                    Twilio::message($order->customer_phone, 'Your order, '.$order->name.' has been confirmed and is in production, with a completion date of '.$arrivalDate.' -District Printing Team');
                }
            } catch (\Exception $e) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            Mail::to($user->email)->send(new OrderProduction($user, $order));
        }

        if(isset($request->production) && $request->production == 'false') {
            $order->status = 'Processing';
        }

        if(isset($request->completed) && $request->completed == 'true') {
            $order->status = 'Completed';
            $user = User::find($order->customer_id);
            Mail::to($user->email)->send(new OrderCompleted($user, $order));
            try {
                if(!$order->customer_phone) {
                    return response([
                        'code' => 400,
                        'success' => false,
                        'message' => "Kindly enter valid phone number"
                    ]);
                }
                if(isset($request->sms) && $request->sms == 'true') {
                    Twilio::message($order->customer_phone, 'Your order  '.$order->name.' is complete and is preparing for shipping. -District Printing Team

                        ');
                }
            } catch (\Exception $e) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

        }

        if(isset($request->completed) && $request->completed == 'false') {
            $order->status = 'Production';
        }

        if($order->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Order updated.',
                'data' => $order
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function OrderIssue(Request $request, $orderId) {
        $order = Order::find($orderId);
        if(auth()->user()->role == 'Sales') {
            $user = Order::where('created_by', auth()->user()->id)->where('id',$orderId)->first();
        }
        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }

        try {
            if(!$order->customer_phone) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' =>'Kindly provide phone number.'
                ]);
            }
            Twilio::message($order->customer_phone, 'There is an issue with your order  '.$order->name.'. Please contact us 310 916 9514. -District Printing Team');
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Order notificaiton sent.',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }


    }

    public function UserDetail(Request $request, $userId) {
        $user = User::find($userId);
        if(auth()->user()->role == 'Sales') {
            $user = User::where('created_by', auth()->user()->id)->where('id',$userId)->first();
        }
        if(!$user) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No user found',
            ]);
        }

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'User detail.',
            'data' => $user
        ]);

    }



    // Update order status
    public function OrderAssignPrinter(Request $request, $orderId) {
        if($request->type === 'in_house') {
            $validator = Validator::make($request->all(), [
                'printer_id' => 'required',
                'printer_schedule' => 'required',
                'printer_duration' => 'required',
                'print_date' => 'required',
            ]);


            if ($validator->fails()) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => Misc::FirstValidationMessage($validator->errors()),
                    'errors' => $validator->errors()
                ]);
            }
        }else if ($request->type === 'out_side') {
            $validator = Validator::make($request->all(), [
                'resource_id' => 'required',
            ]);


            if ($validator->fails()) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => Misc::FirstValidationMessage($validator->errors()),
                    'errors' => $validator->errors()
                ]);
            }
        }


        $order = Order::find($orderId);
        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }


        if(isset($request->printer_id) && !empty($request->printer_id)) {
            $order->printer_id = $request->printer_id;
        }

        if(isset($request->printer_schedule) && !empty($request->printer_schedule)) {
            $order->printer_schedule = $request->printer_schedule;
        }

        if(isset($request->printer_duration) && !empty($request->printer_duration)) {
            $order->printer_duration = $request->printer_duration;
        }

        if(isset($request->print_date) && !empty($request->print_date)) {
            $order->print_date = $request->print_date;
        }

        if(isset($request->printer_id) && !empty($request->printer_id)) {
            $order->printer_id = $request->printer_id;
        }

        if(isset($request->resource_id) && !empty($request->resource_id)) {
            $order->resource_id = $request->resource_id;
        }

        if(isset($request->type) && !empty($request->type)) {
            $order->printer_type = $request->type;
        }

        if($order->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Printer Assigned.',
                'data' => $order
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }



    public function Shipping(Request $request, $orderId) {
        $order = Order::find($orderId);

        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }

        $totalCount = 0;
        for($i = 1; $i <= 5; $i++) {
            if($order['pcs_1_'.$i]) {
                $totalCount += $order['pcs_1_'.$i];
            }
            if($order['pcs_2_'.$i]) {
                $totalCount += $order['pcs_2_'.$i];

            }
            if($order['pcs_3_'.$i]) {
                $totalCount += $order['pcs_3_'.$i];

            }
            if($order['pcs_4_'.$i]) {
                $totalCount += $order['pcs_4_'.$i];
            }

            if($order['xs_'.$i]) {
                $totalCount += $order['xs_'.$i];
            }

            if($order['s_'.$i]) {
                $totalCount += $order['s_'.$i];
            }

            if($order['m_'.$i]) {
                $totalCount += $order['m_'.$i];
            }
            if($order['l_'.$i]) {
                $totalCount += $order['l_'.$i];
            }
            if($order['xxl_'.$i]) {
                $totalCount += $order['xxl_'.$i];
            }
            if($order['xl_'.$i]) {
                $totalCount += $order['xl_'.$i];
            }
            if($order['xxxl_'.$i]) {
                $totalCount += $order['xxxl_'.$i];
            }
        }

        $shipStationResponse = "";

        if(!$order->is_shipping) {
            $user = User::find($order->customer_id);
            try {
                $shipStation = app(\LaravelShipStation\ShipStation::class);

                $address = new \LaravelShipStation\Models\Address();

                $address->name = $order->customer_name;
                $address->city = $order->customer_address_2;
                $address->street1 = $order->customer_address;
                $address->state = $order->customer_state;
                $address->postalCode = $order->customer_zipcode;
                $address->country = "US";
                $address->phone = $order->customer_phone;

                $item = new \LaravelShipStation\Models\OrderItem();

                $item->lineItemKey = $order->id;
                $item->name = $order->name;
                $item->quantity = $totalCount; // TODO Total Quantity
                $item->unitPrice  = '0.00';
                // $item->warehouseLocation = 'Warehouse A';

                $orderDe = new \LaravelShipStation\Models\Order();

                $orderDe->orderNumber = $order->id;
                $orderDe->orderDate = $order->arrival_date;
                $orderDe->orderStatus = 'awaiting_shipment';
                $orderDe->customerEmail = $user->shipping_email ? $user->shipping_email : $user->email;
                $orderDe->amountPaid = '0.00';
                $orderDe->taxAmount = '0.00';
                $orderDe->shippingAmount = '0.00';
                $orderDe->internalNotes = '...';
                $orderDe->billTo = $address;
                $orderDe->shipTo = $address;
                $orderDe->items[] = $item;

                $shipStationResponse =  $shipStation->orders->post($orderDe, 'createorder');

                if(!$order->customer_phone) {
                    return response([
                        'code' => 400,
                        'success' => false,
                        'message' => "Kindly enter valid phone number"
                    ]);
                }
                if(isset($request->sms) && $request->sms == 'true') {
                    Twilio::message($order->customer_phone, 'Your order  '.$order->name.' has been shipped/picked up. -District Printing Team');
                }
            } catch (\Exception $e) {
                return response([
                    'code' => 400,
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        $order->is_shipping = !$order->is_shipping;
        // $order->status = 'Shipping';
        if($shipStationResponse) {
            $order->shipping_order_id = $shipStationResponse->orderId;
        }

        if($order->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Shipping status successfully updated.',
                'data' => $order
            ]);
        } else {
            return response([
                'code' => 200,
                'success' => false,
                'message' => 'Something went wrong. Try again later'
            ]);
        }
    }


    // Resourcea
    public function GetResourcesList(Request $request) {
        $resources = Resource::whereIsDeleted(0)->orderBy('email', 'asc')->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Resources List.',
            'data' => $resources
        ]);
    }

    public function CreateResource(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $resource = new Resource;

        $resource->name = $request->name;
        $resource->email = $request->email;

        if($resource->save()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Resource Added.',
                'data' => $resource
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function UpdateResource(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $resource = Resource::find($id);
        if(!$resource) {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Resource not found.'
            ]);
        }
          $resource->name = $request->name;
        $resource->email = $request->email;

        if($resource->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Resource updated.',
                'data' => $resource
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function DeleteResource(Request $request, $id) {

        $resource = Resource::find($id);
        if(!$resource) {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Printer not found.'
            ]);
        }

        $resource->is_deleted = !$resource->is_deleted;
        if($resource->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Printer deleted.'
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }



    public function GetPrintersList(Request $request) {
        $printers = Printer::whereIsDeleted(0)->orderBy('title', 'asc')->get();
        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Printers List.',
            'data' => $printers
        ]);
    }

    public function CreatePrinter(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $printer = new Printer;

        $printer->title = $request->title;

        if($printer->save()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Printer Added.',
                'data' => $printer
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function UpdatePrinter(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $printer = Printer::find($id);
        if(!$printer) {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Printer not found.'
            ]);
        }
        $printer->title = $request->title;

        if($printer->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Printer updated.',
                'data' => $printer
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function DeletePrinter(Request $request, $id) {

        $printer = Printer::find($id);
        if(!$printer) {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Printer not found.'
            ]);
        }

        $printer->is_deleted = !$printer->is_deleted;


        if($printer->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Printer deleted.'
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

}
