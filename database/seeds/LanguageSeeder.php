<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('languages')->insert([
            [
                'name' => 'English',
                'code' => 'en',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Estonian',
                'code' => 'et',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Finnish',
                'code' => 'fi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Greek',
                'code' => 'el',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Italian',
                'code' => 'it',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
