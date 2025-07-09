<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NftsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('nfts')->delete();
        
        \DB::table('nfts')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Persona Default - Avatar',
                'short_description' => 'Persona Default',
                'description' => 'Persona Default',
                'category' => 'Avatar',
                'subcategory' => 'Gratis',
                'portrait_image' => 'https://welore.io/images/profile/profile-1.jpg',
                'featured_image' => 'https://welore.io/images/profile/profile-1.jpg',
                'token_props' => 0,
                'token_realm' => 0,
                'token_number' => 0,
                'created_at' => '2025-07-01 19:57:37',
                'updated_at' => '2025-07-01 19:57:37',
            ),
        ));
        
        
    }
}