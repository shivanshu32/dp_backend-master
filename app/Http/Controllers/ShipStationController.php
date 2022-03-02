<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Validator;
use App\Models\Misc;
use App\Models\PastLabels;
use App\Mail\OrderProduction;
use Mail;

class ShipStationController extends Controller
{
    public function GetCarriersList(Request $request) {
        $shipStation = app(\LaravelShipStation\ShipStation::class);

        $shipStationResponse =  $shipStation->carriers->get([], '');

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Carriers List',
            'data' => $shipStationResponse
        ]);
    }

    public function GetPackagesList(Request $request, $carrier_code) {
        $shipStation = app(\LaravelShipStation\ShipStation::class);

        $packagesList =  $shipStation->carriers->get([
            'carrierCode' => $carrier_code
        ], 'listpackages');

        $servicesList =  $shipStation->carriers->get([
            'carrierCode' => $carrier_code
        ], 'listservices');

        return response([
            'code' => 200,
            'success' => true,
            'message' => 'Packages List',
            'data' => [
                'packages' => $packagesList,
                'services' => $servicesList,
                'confirmation' => [
                    [
                        'name' => 'None',
                        'value' => 'none'
                    ],
                    [
                        'name' => 'Delivery',
                        'value' => 'delivery'
                    ],
                    [
                        'name' => 'Signature',
                        'value' => 'signature'
                    ], 
                    [
                        'name' => 'Adult Signature',
                        'value' => 'adult_signature'
                    ], 
                    [
                        'name' => 'Direct Signature (Only Fedex)',
                        'value' => 'direct_signature'
                    ], 
                ]
            ]
        ]);
    }


    public function GenerateLabel(Request $request, $orderID) {
        $validator = Validator::make($request->all(), [
            'carriers_code' => 'required',
            'service_code' => 'required',
            'package_code' => 'required',
            'package_size' => 'required',
            'package_length' => 'required',
            'package_width' => 'required',
            'weight_value' => 'required',
            'shipping_confirmation' => 'required',
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
        
        if($order) {
            if(!$order->shipping_order_id) {
                return  response([
                    'code' => 400,
                    'success' => false,
                    'message' => 'Order not availiable for shipping on shipstation',
                ]);
            }

            $order->carriers_code = $request->carriers_code;
            $order->ship_type = $request->service_code;
            $order->package_code = $request->package_code;
            $order->package_size = $request->package_size;
            $order->package_length = $request->package_length;
            $order->package_width = $request->package_width;
            $order->package_unit = 'inches';

            $order->weight_value = $request->weight_value;
            $order->weight_unit = 'pounds';
            $order->shipping_confirmation = $request->shipping_confirmation;

            if($order->update()) {
                try {
                    $shipStation = app(\LaravelShipStation\ShipStation::class);    

                    $weight = new \LaravelShipStation\Models\Weight();
                    $weight->value =  $request->weight_value * 16;
                    $weight->unit = $order->weight_unit;
                    
                    $dimensions = new \LaravelShipStation\Models\Dimensions();
                    $dimensions->length =  $order->package_length;
                    $dimensions->width =  $order->package_width;
                    $dimensions->height =  $order->package_size;
                    $dimensions->units =  $order->package_unit;

                    
                    $shipStationResponse =  $shipStation->orders->post([
                        'orderId' => $order->shipping_order_id,
                        'carrierCode' => $order->carriers_code,
                        'serviceCode' => $order->ship_type,
                        'packageCode' => $order->package_code,
                        'confirmation' => $order->shipping_confirmation,
                        'shipDate' => $order->arrival_date,
                        'weight' => $weight,
                        'dimensions' => $dimensions
                    ], 'createlabelfororder');

                    if($shipStationResponse->labelData !== null) {
                        $folder = $order->id.'/label/label_'. intval(microtime(true)) .'.pdf';
                        $uploadPath = \Storage::disk('s3')->put($folder, base64_decode($shipStationResponse->labelData),'public');
                        $order->shipping_label_url = env('AWS_URL').$folder;

                        $pastLabel = PastLabels::create([
                            'order_id' => $order->id,
                            'label_url' => $order->shipping_label_url
                        ]);


                        if($order->customer_track_url !== null){
                            $order->customer_track_url .= '

';
                        }

                        if(@$shipStationResponse->carrierCode === null){
                            $shipStationResponse->carrierCode = $shipStationResponse->carriers_code;
                        }

                        switch(@$shipStationResponse->carrierCode){
                            case "fedex":
                                $order->customer_track_url .= 'https://www.fedex.com/fedextrack/?action=track&trackingnumber='.$shipStationResponse->trackingNumber; 
                            break;
                            case "stamps_com":
                                $order->customer_track_url .= 'https://tools.usps.com/go/TrackConfirmAction.action?tLabels='.$shipStationResponse->trackingNumber;
                            break;
                            case "ups_walleted":
                                $order->customer_track_url .= 'http://wwwapps.ups.com/WebTracking/processRequest?HTMLVersion=5.0&Requester=NES&AgreeToTermsAndConditions=yes&loc=en_US&tracknum='.$shipStationResponse->trackingNumber;
                            break;
                        }

                        $user = User::find($order->customer_id);

                        $order->update();
                        
                    }


    
                    return response([
                        'code' => 200,
                        'success' => true,
                        'message' => 'Response',
                        'data' => $order,
                        'hasLabelData' => $shipStationResponse->labelData !== null,
                    ]);
                } catch (\Exception $e) {

                    $errorMessage = $e->getMessage();
                    $message = "Error in creating new shipping label.

                    ";

                    if(preg_match("#maximum weight#",$errorMessage)){
                        $message .= "Weight exceeded threshold of selected package. Either select a different service, or a different package.";
                    }else if(preg_match("#FEDEX#",$errorMessage)){
                        $message .= "Unable to create FEDEX package.";
                    }else if(preg_match("#requested service is invalid#",$errorMessage)){
                        $message .= "Wrong shipping service/package configurations. Please ensure that the service you are selecting is correct.";
                    }else{
                        $message = $e->getMessage();
                    }


                    return response([
                        'code' => 400,
                        'success' => false,
                        'message' => $message
                    ]);
                }
            } else {
                return  response([
                    'code' => 500,
                    'success' => false,
                    'message' => 'Something went wrong. Try again.',
                ]);
            }
        } else {
            return  response([
                'code' => 404,
                'success' => false,
                'message' => 'No order found',
            ]);
        }

        $weightUnit = 'pounds';
        $dimensionsUnit = 'inches';
    }
}


// none, delivery, signature, adult_signature, and direct_signature