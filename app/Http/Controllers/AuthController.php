<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Misc;
use Validator, Mail, Hash;
use App\Models\User;
use App\Models\Event;;
use App\Mail\RecoverPassword;

class AuthController extends Controller
{
    // POST::Login
    public function Login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|required',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }
 

        $user = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($user)) {
            
            $user = auth()->user();
            if(!$user->status) {
                return response([
                    'code' => 403,
                    'success' => false,
                    'message' => 'Account not active. Please contact admin for activation.'
                ]);
            }

            $user['access_token'] = auth()->user()->createToken('authToken')->accessToken;
            return response([
                'code' => 200,
                'success' => true,
                'data' => $user
            ]);
        }

        return response([
            'code' => 401,
            'success' => false,
            'message' => 'Invalid Credentials'
        ]);
    }

    //POST::Forgot 
    public function Forgot(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|required|exists:users,email',
            'redirect_url' => 'required|url'
        ]);
        
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = User::whereEmail($request->email)->first();
        $user->forgot_token = Misc::GenerateToken();

        if($user->update()) {
            $redirectURL = $request->redirect_url.$user->forgot_token;
            Mail::to($user->email)->send(new RecoverPassword($user, $redirectURL));

            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Password reset email sent, Plase check your email to reset password.'
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

    // POST::Recover
    public function Recover(Request $request) {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

         
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = User::whereForgotToken($request->token)->first();
        if($user) {
            $user->password = bcrypt($request->password);
            $user->forgot_token = Misc::GenerateToken();
            if($user->update()) {
                return response([
                    'code' => 200,
                    'success' => true,
                    'message' => 'Password updated successfully.'
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
                'message' => 'Password recovery link expired.'
            ]);
        }
    
    }

    // POST::User
    public function User(Request $request) {
        return response([
            'code' => 200,
            'success' => true, 
            'message' => 'User data.',
            'data' => $request->user()
        ]);
    }

    // POST::UpdatePassword

    public function UpdatePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|min:8',
            'password' => 'required|confirmed|min:8',
        ]);

         
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }
    
        $user = $request->user();
        if (Hash::check($request->current_password, $user->password)) {
            
            $user->password = bcrypt($request->password);
            if($user->update()) {
                return response([
                    'code' => 200,
                    'success' => true,
                    'message' => 'Password updated successfully.'
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
                'message' => 'Password mismatch. Please provide correct password.'
            ]);
        }


    }


    // POST::UpdateProfile
    public function UpdateProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'contact_number' => 'max:50',
            'city' => 'max:50',
            'state' => 'max:50',
            'zipcode' => 'max:50',
        ]);

         
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = $request->user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if(isset($request->contact_number)) 
            $user->contact_number = $request->contact_number;

        if(isset($request->address))
            $user->address = $request->address;

        if(isset($request->city)) 
            $user->city = $request->city;

        if(isset($request->email)) 
            $user->email = $request->email;

        if(isset($request->state)) 
            $user->state = $request->state;

        if(isset($request->zipcode)) 
            $user->zipcode = $request->zipcode;

        if($user->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Profile updated successfully.',
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

    public function UpdateShipping(Request $request) {
        $validator = Validator::make($request->all(), [
            'shipping_email' => 'max:100|email',
            'shipping_name' => 'max:50',
            'shipping_street_appartment' => 'max:50',
            'shipping_city' => 'max:50',
            'shipping_state' => 'max:50',
            'shipping_zipcode' => 'max:50',
        ]);

         
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = $request->user();

        if(isset($request->shipping_email)) 
            $user->shipping_email = $request->shipping_email;
        
        if(isset($request->shipping_name)) 
            $user->shipping_name = $request->shipping_name;
        
        if(isset($request->shipping_address)) 
            $user->shipping_address = $request->shipping_address;

        if(isset($request->shipping_street_appartment)) 
            $user->shipping_street_appartment = $request->shipping_street_appartment;

        if(isset($request->shipping_city))
            $user->shipping_city = $request->shipping_city;

        if(isset($request->shipping_state)) 
            $user->shipping_state = $request->shipping_state;

        if(isset($request->shipping_zipcode)) 
            $user->shipping_zipcode = $request->shipping_zipcode;

        if($user->update()) {
            return response([
                'code' => 200,
                'success' => true,
                'message' => 'Shipping info updated successfully.',
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

    public function GetCustomerList(Request $request) {
        if(auth()->user()->role == 'Admin') {
            $users = User::whereRole('Customer')->whereStatus(1);
        } else if(auth()->user()->role == 'Sales') {
            $users = User::whereRole('Customer')->whereCreatedBy(auth()->user()->id)->whereStatus(1);
        } else {
            $users = User::whereRole('Customer')->whereStatus(1);
        }

        if($request->query !== null){
            $users = $users->where(function($query) use($request){
                $query->orWhere('first_name','like','%'.$request->get('query').'%')
                ->orWhere('last_name','like','%'.$request->get('query').'%')
                ->orWhere('email','like','%'.$request->get('query').'%');
            });
        }

        $users = $users->limit(100)->get();
        
        return response([
            'code' => 200,
            'success' => true, 
            'message' => 'Customer List.',
            'data' => $users
        ]);
    }

    public function GetSaleList(Request $request) {
        return response([
            'code' => 200,
            'success' => true, 
            'message' => 'Customer List.',
            'data' => User::whereRole('Sales')->whereStatus(1)->get()
        ]);
    }

    public function GetEventsList(Request $request) {
        return response([
            'code' => 200,
            'success' => true, 
            'message' => 'Events List.',
            'data' => Event::orderBy('id', 'desc')->get()
        ]);
    }

    public function CreateEvent(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'date' => 'required'
        ]);

         
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'success' => false,
                'message' => Misc::FirstValidationMessage($validator->errors()),
                'errors' => $validator->errors()
            ]);
        }

        $user = $request->user();
        $event = new Event;
        $event->user_id = $user->id;
        $event->title = $request->title;
        $event->date = $request->date;

        if($event->save()) {
            return response([
                'code' => 200,
                'success' => true, 
                'message' => 'Events List.',
                'data' => Event::orderBy('id', 'desc')->get()
            ]);
        } else {
            return response([
                'code' => 500,
                'success' => false,
                'message' => 'Something went wrong, Try again later.'
            ]);
        }
    }

    public function DeleteEvent(Request $request, $id) {
        $event = Event::find($id);
        if(!$event) {
            return response([
                'code' => 404,
                'success' => false,
                'message' => 'Event not found'
            ]);
        }

        if($event->delete()) {
            return response([
                'code' => 200,
                'success' => true, 
                'message' => 'Events Deleted.',
                'data' => Event::orderBy('id', 'desc')->get()
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
