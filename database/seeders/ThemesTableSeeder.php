<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ThemesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('themes')->delete();
        
        \DB::table('themes')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Default',
                'logo' => 'https://reports.madfenix.com/welore/demo-assets/logo.png',
                'color_primary' => '#f37322',
                'color_secondary' => '#326b8a',
                'color_white0' => '#ffffff',
                'color_black' => '#161616',
                'color_background_secondary' => '#303030',
                'title_profile' => 'Perfil',
                'title_avatar' => 'Avatar',
                'title_state' => 'Estado',
                'title_coin_premium' => 'Diamante',
                'title_coin_free' => 'Plata',
                'title_polls' => 'Encuestas',
                'title_items' => 'Recursos',
                'title_collections' => 'Colecciones',
                'title_season' => 'Recompensas',
                'title_season_level' => 'Nivel',
                'title_season_points' => 'Puntos',
                'title_season_premium' => 'Premium',
                'title_store' => 'Tienda',
                'title_store_h1' => 'Consigue tus ofertas',
                'icon_polls' => 'https://reports.madfenix.com/welore/demo-assets/polls/polls.png',
                'icon_items' => 'https://reports.madfenix.com/welore/demo-assets/items/items.png',
                'icon_coin_free' => 'https://reports.madfenix.com/welore/demo-assets/coins/coin-free.png',
                'icon_coin_premium' => 'https://reports.madfenix.com/welore/demo-assets/coins/coin-premium.png',
                'icon_rewards_pass' => 'https://reports.madfenix.com/welore/demo-assets/rewards-pass/header2.png',
                'active' => 1,
                'created_at' => '2025-07-01 16:19:18',
                'updated_at' => '2025-07-07 16:01:46',
            ),
        ));
        
        
    }
}