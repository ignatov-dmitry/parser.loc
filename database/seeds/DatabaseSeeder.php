<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        DB::table('platforms')->insert([
            'name' => 'av.by',
            'url'  => 'https://av.by'
        ]);
        DB::table('platforms')->insert([
            'name' => 'kufar',
            'url'  => 'https://auto.kufar.by'
        ]);
    }
}
