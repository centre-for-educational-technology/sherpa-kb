<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('topics')->insert([
            [
                'description' => 'General',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Coordinator',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Tooltechnical',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Privacy',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Schoolrelated',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Assessment',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Educationlevel',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Schoolleader',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Teacher',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Student',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Questions',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'CoreQuestion',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'OptionalQuestion',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'OwnQuestion',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Schoolleader',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Language',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'EducationLevel',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Starting, Links',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Assessmentlength',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Participationrates',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Certificate, Badge',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Results',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'description' => 'Problems',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
