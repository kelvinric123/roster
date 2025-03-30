<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

class MalaysianHolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing holidays first
        Holiday::truncate();

        // 2025 Holidays (both recurring and non-recurring)
        $holidays = [
            // National Holidays (Recurring)
            [
                'name' => 'New Year\'s Day',
                'date' => '2025-01-01',
                'description' => 'First day of the Gregorian calendar year',
                'is_recurring' => true,
            ],
            [
                'name' => 'Federal Territory Day',
                'date' => '2025-02-01',
                'description' => 'Anniversary of the formation of the Federal Territories (Kuala Lumpur, Labuan, Putrajaya)',
                'is_recurring' => true,
            ],
            [
                'name' => 'Labour Day',
                'date' => '2025-05-01',
                'description' => 'International Workers\' Day',
                'is_recurring' => true,
            ],
            [
                'name' => 'Wesak Day',
                'date' => '2025-05-12', // Dates change annually
                'description' => 'Celebration of Buddha\'s birthday',
                'is_recurring' => false,
            ],
            [
                'name' => 'King\'s Birthday',
                'date' => '2025-06-07', // Dates vary based on current Yang di-Pertuan Agong
                'description' => 'Official birthday celebration of the Yang di-Pertuan Agong (King) of Malaysia',
                'is_recurring' => false,
            ],
            [
                'name' => 'National Day',
                'date' => '2025-08-31',
                'description' => 'Malaysia\'s Independence Day (Merdeka Day)',
                'is_recurring' => true,
            ],
            [
                'name' => 'Malaysia Day',
                'date' => '2025-09-16',
                'description' => 'Anniversary of the formation of Malaysia',
                'is_recurring' => true,
            ],
            [
                'name' => 'Deepavali',
                'date' => '2025-10-24', // Dates change annually
                'description' => 'Hindu Festival of Lights',
                'is_recurring' => false,
            ],
            [
                'name' => 'Christmas Day',
                'date' => '2025-12-25',
                'description' => 'Christian celebration of the birth of Jesus Christ',
                'is_recurring' => true,
            ],

            // Islamic Holidays (dates based on lunar calendar, non-recurring)
            [
                'name' => 'Awal Muharram (Islamic New Year)',
                'date' => '2025-07-08', // Approximate date for 2025
                'description' => 'Islamic New Year',
                'is_recurring' => false,
            ],
            [
                'name' => 'Maulidur Rasul (Prophet Muhammad\'s Birthday)',
                'date' => '2025-09-16', // Approximate date for 2025
                'description' => 'Celebration of Prophet Muhammad\'s birthday',
                'is_recurring' => false,
            ],
            [
                'name' => 'Hari Raya Puasa (Eid al-Fitr)',
                'date' => '2025-04-02', // Approximate date for 2025
                'description' => 'Celebration marking the end of Ramadan',
                'is_recurring' => false,
            ],
            [
                'name' => 'Hari Raya Haji (Eid al-Adha)',
                'date' => '2025-06-09', // Approximate date for 2025
                'description' => 'Feast of the Sacrifice',
                'is_recurring' => false,
            ],

            // Chinese New Year (dates change annually)
            [
                'name' => 'Chinese New Year (First Day)',
                'date' => '2025-01-29', // Date for 2025
                'description' => 'First day of the Chinese lunar calendar',
                'is_recurring' => false,
            ],
            [
                'name' => 'Chinese New Year (Second Day)',
                'date' => '2025-01-30', // Date for 2025
                'description' => 'Second day of the Chinese lunar calendar',
                'is_recurring' => false,
            ],
            
            // Thaipusam (dates change annually)
            [
                'name' => 'Thaipusam',
                'date' => '2025-02-03', // Date for 2025
                'description' => 'Hindu festival celebrated during the Tamil month of Thai',
                'is_recurring' => false,
            ],
            
            // Nuzul Al-Quran
            [
                'name' => 'Nuzul Al-Quran',
                'date' => '2025-04-18', // Approximate date for 2025
                'description' => 'Commemoration of the revelation of the Quran to Prophet Muhammad',
                'is_recurring' => false,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create([
                'name' => $holiday['name'],
                'date' => Carbon::parse($holiday['date']),
                'description' => $holiday['description'],
                'is_recurring' => $holiday['is_recurring'],
                'status' => 'active',
            ]);
        }
    }
}
