<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Validator;
use App\Models\Misc;
use App\Models\OrderArt;
use App\Models\User;
use App\Models\PastLabels;
use App\Models\AccessCode;
use App\Mail\ResourceSend;
use Mail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function OrdersList(Request $request) {

        DB::enableQueryLog();

        $query = Order::with('order_arts')->leftJoin('printers', 'orders.printer_id', '=', 'printers.id')
            ->leftJoin('users', 'orders.customer_id', '=', 'users.id');

        $type = $request->type;
        if(isset($request->title)) {
            $query = $query->where(function($q) use ($request, $type) {
                $q->orWhere('orders.name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('orders.id', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('users.first_name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('users.last_name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('orders.invoice_number', 'LIKE', '%'.$request->title.'%');

                if(isset($type) && $type == "purchasing") {
                    $q->orWhere('orders.po_number', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_5', 'LIKE', '%'.$request->title.'%');

                    $q->orWhere('orders.item_number_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_5', 'LIKE', '%'.$request->title.'%');

                    $q->orWhere('orders.product_color_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_5', 'LIKE', '%'.$request->title.'%');
                }
            });
        }

        if(isset($request->customer_id)) {
            $query = $query->where('orders.customer_id', $request->customer_id);
        }

        if(isset($request->startCalendarDate) && isset($request->endCalendarDate) ) {

            $query = $query->where(function($q) use ($request, $type) {
                $q->whereDate('orders.arrival_date', '>=', $request->startCalendarDate);
                $q->whereDate('orders.arrival_date', '<=', $request->endCalendarDate);
            });
        }


        if(isset($request->startCalendarDate) && isset($request->endCalendarDate) && isset($request->calendar)) {

            $query = $query->where(function($q) use ($request, $type) {
                $q->whereDate('orders.arrival_date', '>=', $request->startCalendarDate);
                $q->whereDate('orders.arrival_date', '<=', $request->endCalendarDate);
            });
        }


        if(isset($request->apparel)) {
            $query = $query->where('orders.apparel', $request->apparel);
        }

        if(isset($request->sale_id)) {
            $query = $query->where('orders.created_by', $request->sale_id);
        }

        if(isset($request->print_date)) {
            $query = $query->where('orders.print_date', $request->print_date);
        }

        if(isset($request->film_status)) {
            if($request->film_status == 'not_added') {
                $query = $query->where('orders.film', false);
            } else if($request->film_status == 'film_added') {
                $query = $query->where('orders.film', true);
            } else if($request->film_status == 'all_orders') {
                $query = $query->where('orders.status', 'Processing');
            }
        }

        if(isset($request->printer_id)) {
            $query = $query->where('orders.printer_id', $request->printer_id);
        }

        // Production, Completed, Processing
        if(isset($request->order_status)) {
            $query = $query->where('orders.status', $request->order_status);
        }

        if(isset($request->order_type)) {
            $query = $query->where('orders.type', $request->order_type);
        }

        if(isset($request->date_order)) {
            $query = $query->whereDate('orders.created_at','>=' ,\Carbon\Carbon::now()->subDays($request->date_order));
        }

        if(auth()->user()->role == 'Sales') {
            $query = $query->where('orders.created_by', auth()->user()->id);
        }

        if(auth()->user()->role == 'Production') {
            $query = $query->where('orders.status', 'Production');
        }

        $query = $query->select(['orders.*', 'printers.title as printer_name'])->orderBy('orders.arrival_date', 'ASC');

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $query->get()
        ]);
    }

    public function SaveQRCode($orderID, Request $request){

        $return = ['status' => false];

        try {

            $folder = $orderID.'/qrcode';

            $extension = $request->file->getClientOriginalExtension();
                
            $originalName = $this->filename($request->file);
            $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->file, 'qr.pdf', 'public');
            $return['status'] = true;

        }catch(\Exception $e){
            $return['error'] = $e->getMessage();
        }

        return response()->json($return);

    }

    public function OrdersListV2(Request $request) {

        $query = Order::with('order_arts')->leftJoin('printers', 'orders.printer_id', '=', 'printers.id')
            ->leftJoin('users', 'orders.customer_id', '=', 'users.id')
            ->leftJoin('resources', 'orders.resource_id', '=', 'resources.id')
            ;

        $type = $request->type;
        if(isset($request->title)) {
            $query = $query->where(function($q) use ($request, $type) {
                $q->orWhere('orders.name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('orders.id', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('users.first_name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('users.last_name', 'LIKE', '%'.$request->title.'%');
                $q->orWhere('orders.invoice_number', 'LIKE', '%'.$request->title.'%');

                if(isset($type) && $type == "purchasing") {
                    $q->orWhere('orders.po_number', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.tracking_number', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_apparel_source_5', 'LIKE', '%'.$request->title.'%');

                    $q->orWhere('orders.item_number_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.item_number_5', 'LIKE', '%'.$request->title.'%');

                    $q->orWhere('orders.product_color_1', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_2', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_3', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_4', 'LIKE', '%'.$request->title.'%');
                    $q->orWhere('orders.product_color_5', 'LIKE', '%'.$request->title.'%');
                }
            });
        }

        if(isset($request->customer_id)) {
            $query = $query->where('orders.customer_id', $request->customer_id);
        }

        if(isset($request->apparel)) {
            $query = $query->where('orders.apparel', $request->apparel);
        }

        if(isset($request->sale_id)) {
            $query = $query->where('orders.created_by', $request->sale_id);
        }

        if(isset($request->print_date)) {
            $query = $query->where('orders.print_date', $request->print_date);
        }

        if(isset($request->film_status)) {
            if($request->film_status == 'not_added') {
                $query = $query->where('orders.film', false);
            } else if($request->film_status == 'film_added') {
                $query = $query->where('orders.film', true);
            } else if($request->film_status == 'all_orders') {
                $query = $query->where('orders.status', 'Processing');
            }
        }

        if(isset($request->printer_id)) {
            $query = $query->where('orders.printer_id', $request->printer_id);
        }

        // Production, Completed, Processing
        if(isset($request->order_status)) {
            $query = $query->where('orders.status', $request->order_status);
        }

        if(isset($request->order_type)) {
            $query = $query->where('orders.type', $request->order_type);
        }

        if(isset($request->date_order)) {
            $query = $query->whereDate('orders.created_at','>=' ,\Carbon\Carbon::now()->subDays($request->date_order));
        }

        if(isset($request->po_number)) {
            $query = $query->where('orders.po_number', 'LIKE', '%'.$request->po_number.'%');
        }

        if(isset($request->tracking_number)) {
            $query = $query->where('orders.tracking_number', 'LIKE', '%'.$request->tracking_number.'%');
        }

        if(auth()->user()->role == 'Sales') {
            $query = $query->where('orders.created_by', auth()->user()->id);
        }

        if(auth()->user()->role == 'Production') {
            $query = $query->where('orders.status', 'Production');
        }

        if (isset($request->pay)) {
            $query->where('pay', $request->pay);
        }

        $query = $query->select(['orders.*', 'orders.s_intro_email', 'printers.title as printer_name','resources.name as resource_name'])->orderBy('orders.arrival_date', 'ASC');

        if($request->get('type') === 'purchasing' || $request->get('type') === 'artwork' || $request->get('type') === 'dashboard'){
            $dataToReturn = $query->paginate(6)->toArray();

            $dataToReturn['code'] = 200;
            $dataToReturn['success'] = true;
            $dataToReturn['message'] = 'Orders List.';

            return response()->json($dataToReturn);
        }

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Orders List.',
            'data' => $query->get()
        ]);


    }

    public function getAllLabels(Request $request, $orderID){

        $orderID = intval($orderID);
        $labels = PastLabels::where('order_id',$orderID)->get();

        if(count($labels) === 0){
            //create one if possible

            $orderDetails = Order::where('id',$orderID)->first();

            if($orderDetails->shipping_label_url !== null){
                $pastLabel = PastLabels::create([
                    'order_id' => $orderID,
                    'label_url' => $orderDetails->shipping_label_url
                ]);


            }

            $labels = PastLabels::where('order_id',$orderID)->get();
        }

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'List of Labels for Order ID: ' .$orderID ,
            'data' => $labels
        ]);

    }

    public function GetOrderDetail(Request $request, $orderID) {

        //auth check

        if(!auth('api')->check()){

            //check if validation code is there


            try {

                $accessCodeData = AccessCode::where(['order_id' => $orderID, 'code' => $request->code])->first();

                if($request->code === null || $accessCodeData === null){
                    return response()->json([
                        'code' => 401,
                        'success' => false,
                        'message' => 'Unauthorized access.',
                    ]);
                }




            }catch(\Exception $e){
                return response()->json([

                    'code' => 401,
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ]);
            }

        }

        $order = Order::with(['order_arts', 'printer'])->whereId($orderID)->first();

        if($order) {

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

            $order['total_product_count'] = $totalCount;

            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Order Detail.',
                'data' => $order
            ]);
        } else {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }
    }

    public function DeleteOrder(Request $request, $orderID) {
        $order = Order::find($orderID);
        if($order) {
            if($order->delete()) {
                return response([
                    'code' => 200,
                    'success' => true,
                    'message' => 'Order successfully deleted.'
                ]);
            } else {
                return response([
                    'code' => 500,
                    'success' => false,
                    'message' => 'Something went wrong, Try again later.'
                ]);
            }
        } else {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }
    }

    public function filename($file){
        return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    }

    public function Create(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'name' => 'required|max:150',
            'type' => 'required',
            'arrival_type' => 'required',
            'arrival_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $order = new Order;

        $order->customer_id = $request->customer_id;
        $order->created_by = auth()->user()->id;

        $order->name = $request->name;
        $order->type = $request->type;

        $order->boxes = $request->boxes;
        $order->weight = $request->weight;

        $order->type_notes = $request->type_notes;
        $order->multiple_pages = isset($request->multiple_pages) && $request->multiple_pages == 'true' ? true: false;

        $order->match_proof_color = isset($request->match_proof_color) && $request->match_proof_color == 'true' ? true: false;

        $order->arrival_type = $request->arrival_type;
        $order->arrival_other = $request->arrival_other;
        $order->arrival_date = $request->arrival_date;
        $order->hard_due_date = isset($request->hard_due_date) && $request->hard_due_date == 'true' ? true: false;

        $order->art_is_sized = isset($request->art_is_sized) && $request->art_is_sized == 'true' ? true : false;
        $order->film = isset($request->film) && $request->film == 'true' ? true : false;
        $order->art_notes = $request->art_notes;


        $order->color_1 = $request->color_1;
        $order->color_2 = $request->color_2;
        $order->color_3 = $request->color_3;
        $order->color_4 = $request->color_4;
        $order->color_5 = $request->color_5;
        $order->color_6 = $request->color_6;

        $order->color_1_pantone = $request->color_1_pantone;
        $order->color_2_pantone = $request->color_2_pantone;
        $order->color_3_pantone = $request->color_3_pantone;
        $order->color_4_pantone = $request->color_4_pantone;
        $order->color_5_pantone = $request->color_5_pantone;
        $order->color_6_pantone = $request->color_6_pantone;

        $order->color_notes = $request->color_notes;

        $order->payment_invoice_url = $request->payment_invoice_url;
        $order->payment_notes = $request->payment_notes;
        $order->payment_terms = isset($request->payment_terms) && $request->payment_terms == 'true'  ? true: false;

        $order->ship_type = $request->ship_type;
        $order->ship_notes = $request->ship_notes;
        $order->ship_terms = isset($request->ship_terms) && $request->ship_terms == 'true' ? true: false;


        $order->customer_name = $request->customer_name;
        $order->customer_attn = $request->customer_attn;
        $order->customer_track_url = $request->customer_track_url;
        $order->customer_notes = $request->customer_notes;
        $order->customer_address = $request->customer_address;
        $order->customer_address_2 = $request->customer_address_2;

        $order->customer_state = $request->customer_state;
        $order->customer_zipcode = $request->customer_zipcode;
        $order->customer_email = $request->customer_email;
        $order->customer_phone = $request->customer_phone;




        $order->setup_name = $request->setup_name;
        $order->setup_screen_1 = $request->setup_screen_1;
        $order->setup_screen_2 = $request->setup_screen_2;
        $order->setup_notes = $request->setup_notes;

        $order->proof_notes = $request->proof_notes;

        $order->position_front = $request->position_front;
        $order->position_back = $request->position_back;
        $order->position_right_left = $request->position_right_left;
        $order->position_additional = $request->position_additional;
        $order->position_notes = $request->position_notes;
        $order->match_proof_position = isset($request->match_proof_position) && $request->match_proof_position == 'true' ? true: false;
        $order->invoice_number = $request->invoice_number;

        for($i = 1; $i <= 5; $i++) {
            $order['product_user_type_'.$i] = $request['product_user_type_'.$i];
            $order['product_user_other_type_'.$i] = $request['product_user_other_type_'.$i];
            $order['per_piece_'.$i] = $request['per_piece_'.$i];
            $order['tax_'.$i] = isset($request['tax_'.$i]) && $request['tax_'.$i] == 'true' ? true : false;
            $order['item_number_'.$i] = $request['item_number_'.$i];
            $order['apparel_type_'.$i] = $request['apparel_type_'.$i];
            $order['product_color_'.$i] = $request['product_color_'.$i];
            $order['product_description_'.$i] = $request['product_description_'.$i];
            $order['product_apparel_source_'.$i] = $request['product_apparel_source_'.$i];
            $order['product_apparel_source_other_'.$i] = $request['product_apparel_source_other_'.$i];
            $order['xs_'.$i] = $request['xs_'.$i];
            $order['s_'.$i] = $request['s_'.$i];
            $order['m_'.$i] = $request['m_'.$i];
            $order['l_'.$i] = $request['l_'.$i];
            $order['xl_'.$i] = $request['xl_'.$i];
            $order['xxl_'.$i] = $request['xxl_'.$i];
            $order['xxxl_'.$i] = $request['xxxl_'.$i];
            $order['other_size_1_'.$i] = $request['other_size_1_'.$i];
            $order['other_size_text_1_'.$i] = $request['other_size_text_1_'.$i];
            $order['pcs_1_'.$i] = $request['pcs_1_'.$i];
            $order['other_size_2_'.$i] = $request['other_size_2_'.$i];
            $order['other_size_text_2_'.$i] = $request['other_size_text_2_'.$i];
            $order['pcs_2_'.$i] = $request['pcs_2_'.$i];

            $order['other_size_3_'.$i] = $request['other_size_3_'.$i];
            $order['other_size_text_3_'.$i] = $request['other_size_text_3_'.$i];
            $order['pcs_3_'.$i] = $request['pcs_3_'.$i];
            $order['other_size_4_'.$i] = $request['other_size_4_'.$i];
            $order['other_size_text_4_'.$i] = $request['other_size_text_4_'.$i];
            $order['pcs_4_'.$i] = $request['pcs_4_'.$i];
        }

        if($order->save()) {

            $orderID = $order->id;

            AccessCode::create([
                'order_id' => $orderID,
                'code' => md5(microtime(true))
            ]);


            $orderUpdate = Order::find($orderID);

            try {

                $user = User::find($order->customer_id);
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
                $item->quantity = 0; // TODO Total Quantity
                $item->unitPrice  = '0.00';
                // $item->warehouseLocation = 'Warehouse A';

                $orderDe = new \LaravelShipStation\Models\Order();

                $orderDe->orderNumber = $order->id;
                $orderDe->orderKey = $order->id;
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
                if($shipStationResponse) {
                    $orderUpdate->shipping_order_id = $shipStationResponse->orderId;
                    $orderUpdate->order_key = $orderID;
                }
            } catch (\Exception $e) {
                $orderUpdate->delete();
                return response([
                    'code' => 500,
                    'success' => false,
                    'orderData' => $orderDe,
                    'message' => $e->getMessage()
                ]);
            }

            if($request->shipping_label_url !== null) {

                $folder = $orderID.'/shipping_label';
                $extension = $request->shipping_label_url->getClientOriginalExtension();

                $originalName = $this->filename($request->shipping_label_url);

                $newName = $originalName."_".intval(microtime(true));

                $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->shipping_label_url, $newName.'.'.$extension, 'public');
                $orderUpdate->shipping_label_url = env('AWS_URL').$uploadPath;

                $pastLabel = PastLabels::create([
                    'order_id' => $orderID,
                    'label_url' => $orderUpdate->shipping_label_url
                ]);

            }

            if($request->packing_list_url) {
                $folder = $orderID.'/packing_list';
                $extension = $request->packing_list_url->getClientOriginalExtension();
                $originalName = $this->filename($request->packing_list_url);

                $newName = $originalName."_".intval(microtime(true));
                $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->packing_list_url, $newName.'.'.$extension, 'public');
                $orderUpdate->packing_list_url = env('AWS_URL').$uploadPath;
            }

            if($request->proof_url) {
                $folder = $orderID.'/proof';
                $originalName = $this->filename($request->proof_url);

                $newName = $originalName."_".intval(microtime(true));
                $extension = $request->proof_url->getClientOriginalExtension();
                $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->proof_url, $newName.'.'.$extension, 'public');
                $orderUpdate->proof_url = env('AWS_URL').$uploadPath;
            }

            if($request->artfile) {
                foreach($request->artfile as $file) {
                    $folder = $orderID.'/art';
                    $originalName = $this->filename($file);
                    $fileName = $originalName."_".Misc::GenerateToken(10).'.'.$file->getClientOriginalExtension();
                    $uploadPath = \Storage::disk('s3')->putFileAs($folder, $file, $fileName, 'public');

                    $orderArt = new OrderArt;
                    $orderArt->order_id = $order->id;
                    $orderArt->filename = $fileName;
                    $orderArt->aws_key = $uploadPath;
                    $orderArt->file_url = env('AWS_URL').$uploadPath;
                    $orderArt->save();
                }
            }


            $orderUpdate->update();

            return response([
                'code' => 200,
                'success' => true,
                'message' => 'New order created.',
                'data' => $orderUpdate,
                'shipStation' => $shipStationResponse
            ]);

        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function UploadFilm(Request $request, $orderID) {
        $validator = Validator::make($request->all(), [
            'film_file' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $order = Order::find($orderID);
        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }


        if($request->film_file) {
            $folder = $orderID.'/film_file';
            $extension = $request->film_file->getClientOriginalExtension();
            $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->film_file, 'film_file.'.$extension, 'public');
            $order->film_file = env('AWS_URL').$uploadPath;
        }


        if($order->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Film file uploaded.',
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

    public function thumbDisplay($id){

    }

    public function UpdateOrder(Request $request, $orderID) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'name' => 'required|max:150',
            'type' => 'required',
            'arrival_type' => 'required',
            'arrival_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }


        $order = Order::find($orderID);
        if(!$order) {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }


        $order->customer_id = $request->customer_id;
        // $order->created_by = auth()->user()->id;

        $order->name = $request->name;
        $order->type = $request->type;

        $order->boxes = $request->boxes;
        $order->weight = $request->weight;


        $order->type_notes = $request->type_notes;
        $order->multiple_pages = isset($request->multiple_pages) && ($request->multiple_pages == 'true' || $request->multiple_pages == '1') ? true: false;

        $order->match_proof_color = isset($request->match_proof_color) && ( $request->match_proof_color == 'true' ||  $request->match_proof_color == '1') ? true: false;

        $order->arrival_type = $request->arrival_type;
        $order->arrival_other = $request->arrival_other;
        $order->arrival_date = $request->arrival_date;
        $order->hard_due_date = isset($request->hard_due_date) && ($request->hard_due_date == 'true' || $request->hard_due_date == '1')  ? true: false;

        // $order->art_is_sized = isset($request->art_is_sized) && ($request->art_is_sized == 'true' || $request->art_is_sized = '1') ? true : false;

        if(isset($request->art_is_sized) && ($request->art_is_sized == 'false' || $request->art_is_sized == '0')) {
            $order->art_is_sized = false;
        } else if(isset($request->art_is_sized) && ($request->art_is_sized == 'true' || $request->art_is_sized == '1')) {
            $order->art_is_sized = true;
        }

        if(isset($request->film) && ($request->film == 'false' || $request->film == '0')) {
            $order->film = false;
        } else if(isset($request->film) && ($request->film == 'true' || $request->film == '1')) {
            $order->film = true;
        }


        // $order->film = isset($request->film) && ($request->film == 'true' || $request->film = '1') ? true : false;
        $order->art_notes = $request->art_notes;


        $order->color_1 = $request->color_1;
        $order->color_2 = $request->color_2;
        $order->color_3 = $request->color_3;
        $order->color_4 = $request->color_4;
        $order->color_5 = $request->color_5;
        $order->color_6 = $request->color_6;

        $order->color_1_pantone = $request->color_1_pantone;
        $order->color_2_pantone = $request->color_2_pantone;
        $order->color_3_pantone = $request->color_3_pantone;
        $order->color_4_pantone = $request->color_4_pantone;
        $order->color_5_pantone = $request->color_5_pantone;
        $order->color_6_pantone = $request->color_6_pantone;


        $order->color_notes = $request->color_notes;

        $order->payment_invoice_url = $request->payment_invoice_url;
        $order->payment_notes = $request->payment_notes;
        $order->payment_terms = isset($request->payment_terms) && ($request->payment_terms == 'true' || $request->payment_terms == '1')  ? true: false;

        $order->ship_type = $request->ship_type;
        $order->ship_notes = $request->ship_notes;
        $order->ship_terms = isset($request->ship_terms) && ($request->ship_terms == true || $request->ship_terms == '1')  ? true: false;


        $order->customer_name = $request->customer_name;
        $order->customer_attn = $request->customer_attn;
        $order->customer_track_url = $request->customer_track_url;
        $order->customer_notes = $request->customer_notes;
        $order->customer_address = $request->customer_address;
        $order->customer_address_2 = $request->customer_address_2;
        $order->customer_state = $request->customer_state;
        $order->customer_zipcode = $request->customer_zipcode;
        $order->customer_email = $request->customer_email;
        $order->customer_phone = $request->customer_phone;

        // $folder = $orderID.'/art';
        // $originalName = $this->filename($file);
        // $fileName = $originalName."_".Misc::GenerateToken(10).'.'.$file->getClientOriginalExtension();
        // $uploadPath = \Storage::disk('s3')->putFileAs($folder, $file, $fileName, 'public')

        if($request->shipping_label_url) {
            $folder = $orderID.'/shipping_label';

            $extension = $request->shipping_label_url->getClientOriginalExtension();

            $originalName = $this->fileName($request->shipping_label_url);

            $newName = $originalName."_".intval(microtime(true));

            $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->shipping_label_url, $newName.'.'.$extension, 'public');
            $order->shipping_label_url = env('AWS_URL').$uploadPath;
        }

        if($request->packing_list_url) {
            $folder = $orderID.'/packing_list';
            $extension = $request->packing_list_url->getClientOriginalExtension();
            $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->packing_list_url, 'packing.'.$extension, 'public');
            $order->packing_list_url = env('AWS_URL').$uploadPath;
        }
        if($request->proof_url) {
            $folder = $orderID.'/proof';
            $extension = $request->proof_url->getClientOriginalExtension();
            $uploadPath = \Storage::disk('s3')->putFileAs($folder, $request->proof_url, 'proof.'.$extension, 'public');
            $order->proof_url = env('AWS_URL').$uploadPath;
        }

        $order->setup_name = $request->setup_name;
        $order->setup_screen_1 = $request->setup_screen_1;
        $order->setup_screen_2 = $request->setup_screen_2;
        $order->setup_notes = $request->setup_notes;


        $order->proof_notes = $request->proof_notes;

        $order->position_front = $request->position_front;
        $order->position_back = $request->position_back;
        $order->position_right_left = $request->position_right_left;
        $order->position_additional = $request->position_additional;
        $order->position_notes = $request->position_notes;
        $order->match_proof_position = isset($request->match_proof_position) && ($request->match_proof_position == 'true' || $request->match_proof_position == '1') ? true: false;
        $order->invoice_number = $request->invoice_number;

        for($i = 1; $i <= 5; $i++) {
            $order['product_user_type_'.$i] = $request['product_user_type_'.$i];
            $order['product_user_other_type_'.$i] = $request['product_user_other_type_'.$i];
            $order['per_piece_'.$i] = $request['per_piece_'.$i];
            $order['tax_'.$i] = isset($request['tax_'.$i]) && ($request['tax_'.$i] == 'true' || $request['tax_'.$i] == '1' ) ? true : false;
            $order['item_number_'.$i] = $request['item_number_'.$i];
            $order['apparel_type_'.$i] = $request['apparel_type_'.$i];
            $order['product_color_'.$i] = $request['product_color_'.$i];
            $order['product_description_'.$i] = $request['product_description_'.$i];
            $order['product_apparel_source_'.$i] = $request['product_apparel_source_'.$i];
            $order['product_apparel_source_other_'.$i] = $request['product_apparel_source_other_'.$i];
            $order['xs_'.$i] = $request['xs_'.$i];
            $order['s_'.$i] = $request['s_'.$i];
            $order['m_'.$i] = $request['m_'.$i];
            $order['l_'.$i] = $request['l_'.$i];
            $order['xl_'.$i] = $request['xl_'.$i];
            $order['xxl_'.$i] = $request['xxl_'.$i];
            $order['xxxl_'.$i] = $request['xxxl_'.$i];
            $order['other_size_1_'.$i] = $request['other_size_1_'.$i];
            $order['other_size_text_1_'.$i] = $request['other_size_text_1_'.$i];
            $order['pcs_1_'.$i] = $request['pcs_1_'.$i];
            $order['other_size_2_'.$i] = $request['other_size_2_'.$i];
            $order['other_size_text_2_'.$i] = $request['other_size_text_2_'.$i];
            $order['pcs_2_'.$i] = $request['pcs_2_'.$i];

            $order['other_size_3_'.$i] = $request['other_size_3_'.$i];
            $order['other_size_text_3_'.$i] = $request['other_size_text_3_'.$i];
            $order['pcs_3_'.$i] = $request['pcs_3_'.$i];
            $order['other_size_4_'.$i] = $request['other_size_4_'.$i];
            $order['other_size_text_4_'.$i] = $request['other_size_text_4_'.$i];
            $order['pcs_4_'.$i] = $request['pcs_4_'.$i];
        }

        if($request->artfile) {
            foreach($request->artfile as $file) {
                //var_dump($file);
                $folder = $orderID.'/art';
                $fileName = Misc::GenerateToken(10).'.'.$file->getClientOriginalExtension();
                $uploadPath = \Storage::disk('s3')->putFileAs($folder, $file, $fileName, 'public');

                $orderArt = new OrderArt;
                $orderArt->order_id = $orderID;
                $orderArt->filename = $fileName;
                $orderArt->aws_key = $uploadPath;
                $orderArt->file_url = env('AWS_URL').$uploadPath;
                $orderArt->save();
            }
        }

        if($order->update()) {
            if($order->shipping_order_id && $order->order_key) {
                try {
                    $user = User::find($order->customer_id);
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
                    $item->quantity = 0; // TODO Total Quantity
                    $item->unitPrice  = '0.00';
                    // $item->warehouseLocation = 'Warehouse A';

                    $orderDe = new \LaravelShipStation\Models\Order();

                    $orderDe->orderKey = $order->id;
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
                } catch (\Exception $e) {
                    return response([
                        'code' => 400,
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }

            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Order updated.',
                'data' => $order,
                'shipstationData' => $shipStationResponse
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }

    }

    public function DeleteFile(Request $request, $orderID, $fileID){
        $file = OrderArt::whereOrderId($orderID)->whereId($fileID)->first();
        if($file) {
            \Storage::disk('s3')->delete($file->aws_key);
            if($file->delete()) {
                return response([
                    'code' => 204,
                    'success' => true,
                    'message' => 'File deleted successfully.'
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
                'code' => 404,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

    public function CreateShippingLabel(Request $request, $orderID) {

    }

    public function SendToOutsideResource(Request $request, $orderID) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'file' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $order = Order::find($orderID);
        $email = $request->email;
        $body = $request->body;
        $file = $request->file;

        if(count(AccessCode::where('order_id',$orderID)->get()) === 0){

            AccessCode::create([
                'order_id' => $orderID,
                'code' => md5(microtime(true))
            ]);

        }

        Mail::to($email)->send(new ResourceSend($email, $body, $file, $order));

        return response([
            'code' => 204,
            'success' => true,
            'message' => 'Resource successfully sent.'
        ]);
    }
 }
