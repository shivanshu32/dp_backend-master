<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', 'AuthController@Login');
Route::post('/forgot', 'AuthController@Forgot');
Route::post('/recover', 'AuthController@Recover');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', 'AuthController@User');

    Route::prefix('admin')->middleware('role:Admin,Sales')->group(function (){
        Route::post('printers/create', 'AdminController@CreatePrinter');
        Route::post('printers/{id}/update', 'AdminController@UpdatePrinter');
        Route::get('printers/{id}/delete', 'AdminController@DeletePrinter');
        Route::post('resources/create', 'AdminController@CreateResource');
        Route::post('resources/{id}/update', 'AdminController@UpdateResource');
        Route::get('resources/{id}/delete', 'AdminController@DeleteResource');
        Route::get('users/list', 'AdminController@GetUsersList');
        Route::post('users/create', 'AdminController@CreateUser');
        Route::get('users/{userId}/details', 'AdminController@UserDetail');
        Route::post('users/{userId}/update', 'AdminController@UserUpdate');
        Route::get('users/{userId}/status', 'AdminController@UserStatus');
        Route::get('users/{userId}/delete', 'AdminController@DeleteUser');
        Route::post('orders/{orderId}/status', 'AdminController@UpdateOrderStatus');
        Route::get('orders/{orderId}/shipping', 'AdminController@Shipping');
        Route::get('orders/{orderId}/issue', 'AdminController@OrderIssue');
        Route::post('orders/{orderId}/assign-printer', 'AdminController@OrderAssignPrinter');
    });

    Route::prefix('admin')->middleware('role:Admin,Sales,Production')->group(function (){
        Route::get('printers/list', 'AdminController@GetPrintersList');
        Route::get('resources/list', 'AdminController@GetResourcesList');
    });


    Route::prefix('customer')->middleware('role:Customer')->group(function(){
        Route::get('orders/production', 'CustomerController@GetProductionOrder');
        Route::get('orders/completed', 'CustomerController@GetCompletedOrder');
        Route::get('orders/{orderID}/delete', 'CustomerController@DeleteCustomerOrder');
    });

    Route::prefix('production')->middleware('role:Production')->group(function(){
        Route::get('orders/list', 'ProductionController@GetProductionOrders');
    });

    Route::prefix('sales')->middleware('role:Sales')->group(function(){
        Route::get('orders/list', 'SalesController@GetOrdersList');
    });


    Route::get('/users/list', 'AuthController@GetCustomerList');
    Route::get('/sales/list', 'AuthController@GetSaleList');
    Route::post('/order', 'OrderController@Create');
    Route::get('/orders/list', 'OrderController@OrdersList');
    
    Route::get('/v2/orders/list', 'OrderController@OrdersListV2');

    Route::get('/orders/{orderID}', 'OrderController@GetOrderDetail');

    Route::get('/orders/{orderID}/delete', 'OrderController@DeleteOrder');

    Route::get('/orders/{orderID}/get-labels', 'OrderController@getAllLabels');

    Route::post('/orders/{orderID}/update', 'OrderController@UpdateOrder');
    Route::post('/orders/{orderID}/upload-film', 'OrderController@UploadFilm');
    Route::get('/orders/{orderID}/file/{fileID}', 'OrderController@DeleteFile');
  
    Route::post('/orders/{orderID}/shipping_label', 'OrderController@CreateShippingLabel');
    Route::post('/orders/{orderID}/resource_sending', 'OrderController@SendToOutsideResource');

    Route::post('/create_qr_code/{orderID}','OrderController@SaveQRCode');


    Route::get('/events', 'AuthController@GetEventsList');
    Route::post('/events', 'AuthController@CreateEvent');
    Route::get('/events/{id}/delete', 'AuthController@DeleteEvent');


    Route::get('/carriers', 'ShipStationController@GetCarriersList');
    Route::get('/packages/{carrier_code}', 'ShipStationController@GetPackagesList');
    Route::post('/generate_label/{orderID}', 'ShipStationController@GenerateLabel');

    Route::post('/update-password', 'AuthController@UpdatePassword');
    Route::post('/update-profile', 'AuthController@UpdateProfile');
    Route::post('/update-shipping', 'AuthController@UpdateShipping');
});

Route::get('/orders/{orderID}', 'OrderController@GetOrderDetail');
