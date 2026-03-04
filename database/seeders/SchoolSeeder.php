<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'id' => 1,
                'ibe_id' => 1,
                'name' => 'A J Mitchell Elementary School Nogales Unified District',
                'type' => 'public',
            ],
            [
                'id' => 2,
                'ibe_id' => 2,
                'name' => 'A. C. E. Marana Unified District',
                'type' => 'public',
            ],
            [
                'id' => 3,
                'ibe_id' => 3,
                'name' => 'A+ Charter Schools A+ Charter Schools',
                'type' => 'public',
            ],
            [
                'id' => 4,
                'ibe_id' => 4,
                'name' => 'AAEC - Paradise Valley Arizona Agribusiness & Equine Center, Inc.',
                'type' => 'public',
            ],
            [
                'id' => 5,
                'ibe_id' => 5,
                'name' => 'AAEC - Smcc Campus Arizona Agribusiness & Equine Center, Inc.',
                'type' => 'public',
            ],
            [
                'id' => 6,
                'ibe_id' => 6,
                'name' => 'AAEC Online Arizona Agribusiness & Equine Center Inc.',
                'type' => 'public',
            ],
            [
                'id' => 7,
                'ibe_id' => 7,
                'name' => 'AAEC Show Low Arizona Agribusiness & Equine Center, Inc.',
                'type' => 'public',
            ],
            [
                'id' => 8,
                'ibe_id' => 8,
                'name' => 'Abia Judd Elementary School Prescott Unified District',
                'type' => 'public',
            ],
            [
                'id' => 10,
                'ibe_id' => 10,
                'name' => 'Abraham Lincoln Traditional School Washington Elementary School District',
                'type' => 'public',
            ],
        ];

        foreach ($schools as $school) {
            School::firstOrCreate(
                ['id' => $school['id']],
                $school
            );
        }
    }
}
