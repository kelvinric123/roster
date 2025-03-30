<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Internal Medicine Department',
                'code' => 'IMD',
                'description' => 'Internal Medicine Department handles diagnosis and treatment of internal diseases.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Surgery Department',
                'code' => 'SURGR',
                'description' => 'Department responsible for surgical procedures and post-operative care.',
                'roster_type' => 'oncall',
            ],
            [
                'name' => 'Pediatrics Department',
                'code' => 'PAED',
                'description' => 'Department specialized in healthcare for children.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Orthopedics Department',
                'code' => 'ORTHO',
                'description' => 'Department specialized in diagnosis and treatment of musculoskeletal system problems.',
                'roster_type' => 'oncall',
            ],
            [
                'name' => 'Obstetrics & Gynecology Department',
                'code' => 'O&G',
                'description' => 'Department specialized in women\'s care during pregnancy and after childbirth.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Cardiology Department',
                'code' => 'KARD',
                'description' => 'Department specialized in diagnosis and treatment of heart diseases.',
                'roster_type' => 'oncall',
            ],
            [
                'name' => 'Neurology Department',
                'code' => 'NEURO',
                'description' => 'Department specialized in diagnosis and treatment of nervous system disorders.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Emergency Department',
                'code' => 'EMRG',
                'description' => 'Department that handles emergency cases and trauma.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Intensive Care Unit (ICU)',
                'code' => 'ICU',
                'description' => 'Unit that provides intensive care for critically ill patients.',
                'roster_type' => 'shift',
            ],
            [
                'name' => 'Psychiatry Department',
                'code' => 'PSYCH',
                'description' => 'Department specialized in diagnosis and treatment of mental health problems.',
                'roster_type' => 'oncall',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
