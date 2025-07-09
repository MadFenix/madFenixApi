<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SeasonRewardsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('season_rewards')->delete();
        
        \DB::table('season_rewards')->insert(array (
            0 => 
            array (
                'id' => 1,
                'level' => 1,
                'required_points' => 14999,
                'oro' => 0,
                'plumas' => 2,
                'nft_id' => NULL,
                'max_nft_rewards' => 0,
                'custom_reward' => NULL,
                'season_id' => 1,
                'created_at' => '2025-07-01 16:16:18',
                'updated_at' => '2025-07-01 16:16:18',
            ),
            1 => 
            array (
                'id' => 2,
                'level' => 2,
                'required_points' => 29999,
                'oro' => 0,
                'plumas' => 2,
                'nft_id' => NULL,
                'max_nft_rewards' => 0,
                'custom_reward' => NULL,
                'season_id' => 1,
                'created_at' => '2025-07-01 16:16:40',
                'updated_at' => '2025-07-01 16:16:40',
            ),
            2 => 
            array (
                'id' => 3,
                'level' => 3,
                'required_points' => 44999,
                'oro' => 0,
                'plumas' => 5,
                'nft_id' => NULL,
                'max_nft_rewards' => 0,
                'custom_reward' => NULL,
                'season_id' => 1,
                'created_at' => '2025-07-01 16:16:58',
                'updated_at' => '2025-07-01 16:16:58',
            ),
            3 => 
            array (
                'id' => 4,
                'level' => 4,
                'required_points' => 59999,
                'oro' => 0,
                'plumas' => 3,
                'nft_id' => NULL,
                'max_nft_rewards' => 0,
                'custom_reward' => NULL,
                'season_id' => 1,
                'created_at' => '2025-07-01 16:17:13',
                'updated_at' => '2025-07-01 16:17:13',
            ),
            4 => 
            array (
                'id' => 5,
                'level' => 5,
                'required_points' => 74999,
                'oro' => 0,
                'plumas' => 10,
                'nft_id' => NULL,
                'max_nft_rewards' => 0,
                'custom_reward' => NULL,
                'season_id' => 1,
                'created_at' => '2025-07-01 16:17:32',
                'updated_at' => '2025-07-01 16:17:32',
            ),
        ));
        
        
    }
}