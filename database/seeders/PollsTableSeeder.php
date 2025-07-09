<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PollsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('polls')->delete();
        
        \DB::table('polls')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'La tribu',
                'short_description' => NULL,
                'description' => '<div class="w-full flex items-end justify-center">
<div>
<h6 class="text-madfenix-blanco w-full text-center" style="line-height: 27px; font-size: 20px !important;">¿Quieres formar parte de la tribu?</h6>
<ul class="py-6">
<li>Sí.</li>
<li>No.</li>
</ul>
<br>
</div>
</div>',
                'portrait_image' => 'https://reports.madfenix.com/welore/demo-assets/rewards-pass/header.png',
                'featured_image' => 'https://reports.madfenix.com/welore/demo-assets/rewards-pass/header.png',
                'answers' => 'Sí,No',
                'start_date' => '2025-07-06 15:52:00',
                'end_date' => '2034-12-31 15:52:00',
                'created_at' => '2025-07-07 15:52:18',
                'updated_at' => '2025-07-07 15:53:58',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'La tribu',
                'short_description' => NULL,
                'description' => '<div class="w-full flex items-end justify-center">
<div>
<h6 class="text-madfenix-blanco w-full text-center" style="line-height: 27px; font-size: 20px !important;">Comparte tu nombre de usuario en instagram con la comunidad</h6>
<br>
</div>
</div>',
                'portrait_image' => 'https://reports.madfenix.com/welore/demo-assets/rewards-pass/header2.png',
                'featured_image' => 'https://reports.madfenix.com/welore/demo-assets/rewards-pass/header2.png',
                'answers' => NULL,
                'start_date' => '2025-07-06 15:57:00',
                'end_date' => '2034-12-07 15:57:00',
                'created_at' => '2025-07-07 15:57:17',
                'updated_at' => '2025-07-07 15:57:17',
            ),
        ));
        
        
    }
}