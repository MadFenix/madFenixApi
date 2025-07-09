<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CouponsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('coupons')->delete();
        
        \DB::table('coupons')->insert(array (
            0 => 
            array (
                'id' => 1,
                'coupon' => 'inicial',
                'plumas' => 25,
                'uses' => 1,
                'max_uses' => 20,
                'start_date' => '2025-06-30 19:54:00',
                'end_date' => '2038-12-25 19:54:02',
                'created_at' => '2025-07-01 19:54:05',
                'updated_at' => '2025-07-01 19:55:13',
            ),
        ));
        
        
    }
}