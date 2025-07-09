<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NftIdentificationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('nft_identifications')->delete();

        \DB::table('nft_identifications')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Persona Default',
                'description' => 'Persona Default',
                'image' => 'https://welore.io/images/profile/profile-1.jpg',
                'nft_identification' => 0,
                'nft_id' => 1,
                'rarity' => NULL,
                'tag_1' => NULL,
                'tag_2' => NULL,
                'tag_3' => NULL,
                'user_id' => 1,
                'user_id_hedera' => NULL,
                'madfenix_ownership' => 0,
                'created_at' => '2025-07-01 19:59:22',
                'updated_at' => '2025-07-01 20:05:15',
            ),
        ));


    }
}
