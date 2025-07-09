<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SeasonsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('seasons')->delete();
        
        \DB::table('seasons')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'First season',
                'max_level' => 5,
                'max_points' => 75000,
                'start_date' => '2025-06-30 00:00:00',
                'end_date' => '2034-12-15 17:00:00',
                'created_at' => '2025-07-01 16:15:43',
                'updated_at' => '2025-07-01 16:15:43',
            ),
        ));
        
        
    }
}