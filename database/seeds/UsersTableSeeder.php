<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Superadmin',
            'email' => 'superadmin@mail.me',
            'password' => bcrypt('Admin123'),
            'type' => config('constants.userType.superadmin'),
            'status' => config('constants.userStatus.active'),
            'msisdn' => '6285693384247',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('users')->insert([
            'name' => 'Salesman 1',
            'email' => 'salesman1@mail.me',
            'password' => bcrypt('Admin123'),
            'type' => config('constants.userType.salesman'),
            'status' => config('constants.userStatus.active'),
            'msisdn' => '6285693384248',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        DB::table('users')->insert([
            'name' => 'Salesman 2',
            'email' => 'salesman2@mail.me',
            'password' => bcrypt('Admin123'),
            'type' => config('constants.userType.salesman'),
            'status' => config('constants.userStatus.active'),
            'msisdn' => '6285693384249',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
