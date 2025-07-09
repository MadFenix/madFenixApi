<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('account_settings')->delete();
        
        \DB::table('account_settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'page_builder_sessions_used' => 5,
                'page_builder_sessions_total' => 10,
                'created_at' => '2025-07-01 16:21:21',
                'updated_at' => '2025-07-01 16:21:22',
            ),
        ));
        
        
    }
}