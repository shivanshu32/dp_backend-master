<?php

namespace App\Models;

class Misc
{
    // Generate Random 6 Digit OTP Code
    public static function RandomOTP() {
        return mt_rand(100000, 999999);
    }

    public static function FirstValidationMessage($errors) {
        $firstErrorMessageKey = null; 
        foreach ($errors->getMessages()  as $key => $value) {
            $firstErrorMessageKey = $key;
            break;
        }
        return $errors->first($firstErrorMessageKey);
    }

    // Generate Random String
    public static function GenerateToken($strength = 60) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

}
