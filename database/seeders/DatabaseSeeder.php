<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::whereEmail('admin@districtprinting.com')->first();
        if(!$admin) {
            $user = new User;
            $user->first_name = 'Admin';
            $user->last_name = 'User';
            $user->email = 'admin@districtprinting.com';
            $user->password = bcrypt('pass1234');
            $user->role = 'Admin';
            $user->contact_number = '+1234566778';
            $user->address = 'Street # 10 House # 5';
            $user->city = 'Texas';
            $user->save();
        } 

        $admin = User::whereEmail('sales@districtprinting.com')->first();
        if(!$admin) {
            $user = new User;
            $user->first_name = 'Sales';
            $user->last_name = 'User';
            $user->email = 'sales@districtprinting.com';
            $user->password = bcrypt('pass1234');
            $user->role = 'Sales';
            $user->contact_number = '+1234566778';
            $user->address = 'Street # 10 House # 5';
            $user->city = 'Texas';
            $user->save();
        } 

        $admin = User::whereEmail('production@districtprinting.com')->first();
        if(!$admin) {
            $user = new User;
            $user->first_name = 'Production';
            $user->last_name = 'User';
            $user->email = 'production@districtprinting.com';
            $user->password = bcrypt('pass1234');
            $user->role = 'Production';
            $user->contact_number = '+1234566778';
            $user->address = 'Street # 10 House # 5';
            $user->city = 'Texas';
            $user->save();
        } 

        $admin = User::whereEmail('customer@districtprinting.com')->first();
        if(!$admin) {
            $user = new User;
            $user->first_name = 'Customer';
            $user->last_name = 'User';
            $user->email = 'customer@districtprinting.com';
            $user->password = bcrypt('pass1234');
            $user->role = 'Customer';
            $user->contact_number = '+1234566778';
            $user->address = 'Street # 10 House # 5';
            $user->city = 'Texas';
            $user->save();
        } 
    }
}
