<?php

    $trackingUrls = explode("\n",$order->customer_track_url);

    $customerTrackUrl = "";

    foreach($trackingUrls as $trackingUrl){
        if(strlen($trackingUrl) > 10){
            $customerTrackUrl .= "<a href=\"$trackingUrl\">Tracking Link</a><br>";
        }
        
    }

    $shippingLabelUrls = explode("\n",$order->shipping_label_url);

    $shippingLabelUrl = "";

    $bucketHost = "https://". config('filesystems.disks.s3.bucket') .".s3.". config('filesystems.disks.s3.region') .".amazonaws.com/";

    foreach($shippingLabelUrls as $label){
        $shippingLabelUrl .= $bucketHost . $label . "<br>";
    }

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>District Printing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
  </head>
  <body style="margin: 0;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #212529;
  box-sizing: border-box;
  font-family: sans-serif;
  text-align: left;
  background-color: #fff;">

    <div style="
        float: none;
        box-sizing: border-box;
        width: 600px;
        margin: 0 auto;
        background: #f7f9f9;
        padding-bottom: 70px;
        ">
        <div  style="float: left;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
        padding-top: 20px;">
        </div>
        <div style="float: left;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
        background: #4ebec4;
        padding-top: 60px;">
            <img src="{{asset('img/logo-thank.png')}}" alt="" />
            <div  style="float: left;
            box-sizing: border-box;
            width: 100%;
            color: #fff;
            margin-top: 60px;
            margin-bottom: 50px;">
                <h2 style="float: left;
                width: 100%;
                box-sizing: border-box;
                color: #fff;
                font-weight: bold;
                font-size: 2rem;
                margin: 9px 0;">Hello, here is an order from our customer</h2>
            </div>
            <div style="width: 100%; float:left;"><img style="float: none;
                display: inline-block;
                box-sizing: border-box;
                margin-bottom: -4px;
                max-width: 100%;" src="{{asset('img/vendor.png')}}" alt=""></div>
        </div>
        <div  style="float: left;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
        background: #f7f9f9;
        padding-top: 80px;">
            <div  style="max-width: 1140px; width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            box-sizing: border-box;
            margin-right: auto;
            margin-left: auto;">
                <h3  style="float: left;
                width: 100%;
                box-sizing: border-box;
                font-weight: bold;
                color: #24a94a;
                margin-top: 0;
                font-size: 1.75rem;
                margin-bottom: 30px;">Job Name: {{$order->name}}</h3>
                <div style="float: left; box-sizing: border-box;
                width: 100%;">
                    <div style="
                    box-sizing: border-box;
                    margin-right: -15px;
                    margin-left: -15px;">
                        <div style=" position: relative;
                            width: 100%;
                            padding-right: 15px;
                            box-sizing: border-box;
                            padding-left: 15px;">
                            <div style="float: left;
                            width: 100%;
                            box-sizing: border-box;
                            background: #fff;
                            border: 1px solid #d6d6d6;
                            box-shadow: 0px 3px 0px rgba(0,0,0,0.05);
                            border-radius: 6px;
                            padding: 33px 0;">
                                <h3 style="float: left;
                                width: 100%;
                                font-size: 16px;
                                box-sizing: border-box;
                                margin-top: 0;
                                font-weight: bold;
                                letter-spacing: 0px;
                                margin-bottom: 2px;">Date Of Delivery: </h3>
                                <span style="float: left;
                                width: 100%;
                                box-sizing: border-box;
                                font-size: 14px;
                                color: #000;">{{date("m d Y", strtotime($order->arrival_date))}}</span>
                            </div>
                        </div>
                        <div style="position: relative;
                            width: 100%;
                            box-sizing: border-box;
                            padding-right: 15px;
                            padding-left: 15px;">

                            @if(!preg_match("#^[ ]{0,}$#",$customerTrackUrl))

                            <div style="float: left;
                            width: 100%;
                            box-sizing: border-box;
                            background: #fff;
                            border: 1px solid #d6d6d6;
                            box-shadow: 0px 3px 0px rgba(0,0,0,0.05);
                            border-radius: 6px;
                            padding: 33px 0;"> 
                                <h3 style="float: left;
                                width: 100%;
                                font-size: 16px;
                                box-sizing: border-box;
                                font-weight: bold;
                                margin-top: 0;
                                letter-spacing: 0px;
                                margin-bottom: 2px;">Tracking URL's</h3>
                                <span style="float: left;
                                width: 100%;
                                box-sizing: border-box;
                                font-size: 14px;
                                color: #000;">{!! $customerTrackUrl !!}</span>
                            </div>

                            @endif

                            @if(!preg_match("#^[ ]{0,}$#",$shippingLabelUrl))

                            <div style="float: left;
                            width: 100%;
                            box-sizing: border-box;
                            background: #fff;
                            border: 1px solid #d6d6d6;
                            box-shadow: 0px 3px 0px rgba(0,0,0,0.05);
                            border-radius: 6px;
                            padding: 33px 0;">
                                <h3 style="float: left;
                                width: 100%;
                                font-size: 16px;
                                box-sizing: border-box;
                                font-weight: bold;
                                margin-top: 0;
                                letter-spacing: 0px;
                                margin-bottom: 2px;">Shipping Label URL's</h3>
                                <span style="float: left;
                                width: 100%;
                                box-sizing: border-box;
                                font-size: 14px;
                                color: #000;">{!! $shippingLabelUrl !!}</span>
                            </div>

                            @endif

                        </div>
                        <div style="position: relative;
                            width: 100%;
                            box-sizing: border-box;
                            padding-right: 15px;
                            padding-left: 15px;">
                            <div style="float: left;
                            width: 100%;
                            box-sizing: border-box;
                            background: #fff;
                            border: 1px solid #d6d6d6;
                            box-shadow: 0px 3px 0px rgba(0,0,0,0.05);
                            border-radius: 6px;
                            padding: 33px 0;">
                                <h3 style="float: left;
                                width: 100%;
                                font-size: 16px;
                                box-sizing: border-box;
                                font-weight: bold;
                                margin-top: 0;
                                letter-spacing: 0px;
                                margin-bottom: 2px;">Order #</h3>
                                <span style="float: left;
                                width: 100%;
                                box-sizing: border-box;
                                font-size: 14px;
                                color: #000;">{{$order->id}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <h3 style="float: left;
                width: 100%;
                box-sizing: border-box;
                color: #a92424; margin-top: 3rem !important; margin-bottom: 0.5rem !important; font-size: 20px;"><a href='http://54.67.32.201/order-details?id={{ $order->id }}&code={{ $code }}'>Click Here To View More Details</a></h3>


                <h3 style="float: left;
                width: 100%;
                box-sizing: border-box;
                color: #24a94a; margin-top: 3rem !important; margin-bottom: 0.5rem !important; font-size: 20px;">Kindly review the order details and send us the completed date.</h3>

                <h3  style="float: left;
                width: 100%;
                box-sizing: border-box;
                font-weight: bold;
                color: #24a94a; margin-top: 3rem !important; margin-bottom: 0.5rem !important; font-size: 20px;">Please find the attached final draft for your record.</h3>

                <h5 style="margin-bottom: 20px; box-sizing: border-box;
                color: #101010; font-size: 16px;">We would like to thank you for your order. If you have any questions, feel free call us at <strong>(310) 916-9514</strong> </h5>

                <h5 style="margin-top: 3rem !important; box-sizing: border-box; margin-bottom: 0.5rem !important; font-size: 1.25rem;">Best Regards,</h5>
                <h3 style="font-size: 1.75rem;">District Printing Support Team</h3>
                <div  style="float: left;
                width: 100%;
                box-sizing: border-box;
                background: #eaeaea;
                border-radius: 6px;
                padding: 30px 0;
                border: 1px solid #d8dcdc;
                margin-bottom: 40px;
                margin-top: 20px;">
                    <p style="float: left;
                    box-sizing: border-box;
                    width: 100%;
                    margin-bottom: 0;
                    font-size: 18px;
                    color: #000;">Please do not reply to this email. This mailbox is not monitored and you will not receive a response.</p> 

                </div>
                <span  style="box-sizing: border-box; padding-bottom: 3rem !important; padding-top: 3rem !important; width: 100% !important; width: 100% !important;">2020 © District Printing - 4722 Normandie Ave, Los Angeles, CA 90037</span>
            </div>
        </div>
    </div><!-- Main Wrap -->
  </body>
</html>
