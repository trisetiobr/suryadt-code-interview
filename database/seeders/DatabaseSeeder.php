<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // this will be clean the users table, I put this to seeder to testing purposes only
        DB::table('users')->delete();
        \App\Models\User::factory(20)->create();
    }
}
