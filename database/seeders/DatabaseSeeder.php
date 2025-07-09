<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(AccountSettingsTableSeeder::class);
        $this->call(CouponsTableSeeder::class);
        $this->call(NftIdentificationsTableSeeder::class);
        $this->call(NftsTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(PollsTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
        $this->call(SeasonRewardsTableSeeder::class);
        $this->call(SeasonsTableSeeder::class);
        $this->call(ThemeConfigsTableSeeder::class);
        $this->call(ThemesTableSeeder::class);
    }
}
