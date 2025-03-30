<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DepartmentSeeder::class,
            DepartmentStaffTypeRosterSeeder::class,
            StaffSeeder::class,
            DepartmentStaffSeeder::class,
            MedicalDepartmentStaffSeeder::class,
            MalaysianHolidaysSeeder::class,
        ]);
    }
}
