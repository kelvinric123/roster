<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class MedicalDepartmentStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder ensures the medical department exists first, then seeds staff under it.
     */
    public function run(): void
    {
        // First, ensure the Medical Department exists
        $medicalDepartment = Department::firstOrCreate(
            ['code' => 'MED'],
            [
                'name' => 'Medical Department',
                'description' => 'General medical department handling various medical cases and specialties.',
                'roster_type' => 'shift',
                'is_active' => true,
            ]
        );

        $this->command->info('Medical Department created or found successfully.');

        // Now create medical staff in this department
        $medicalStaff = [
            // Specialist Doctors
            [
                'name' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@hospital.com',
                'type' => Staff::TYPE_SPECIALIST_DOCTOR,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(5),
                'phone' => '6012345678',
                'is_active' => true,
                'specialization' => 'Cardiology',
                'qualification' => 'MD, FRCP',
            ],
            [
                'name' => 'Dr. Michael Chen',
                'email' => 'michael.chen@hospital.com',
                'type' => Staff::TYPE_SPECIALIST_DOCTOR,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(7),
                'phone' => '6023456789',
                'is_active' => true,
                'specialization' => 'Gastroenterology',
                'qualification' => 'MD, PhD',
            ],
            
            // Medical Officers
            [
                'name' => 'Dr. Aisha Rahman',
                'email' => 'aisha.rahman@hospital.com',
                'type' => Staff::TYPE_MEDICAL_OFFICER,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(2),
                'phone' => '6034567890',
                'is_active' => true,
                'department' => 'General Medicine',
            ],
            [
                'name' => 'Dr. John Lee',
                'email' => 'john.lee@hospital.com',
                'type' => Staff::TYPE_MEDICAL_OFFICER,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(3),
                'phone' => '6045678901',
                'is_active' => true,
                'department' => 'Critical Care',
            ],
            
            // Houseman Officers
            [
                'name' => 'Dr. Emily Wong',
                'email' => 'emily.wong@hospital.com',
                'type' => Staff::TYPE_HOUSEMAN_OFFICER,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subMonths(8),
                'phone' => '6056789012',
                'is_active' => true,
                'current_rotation' => 'Internal Medicine',
                'graduation_year' => now()->subYear()->year,
            ],
            [
                'name' => 'Dr. Ahmad Hafiz',
                'email' => 'ahmad.hafiz@hospital.com',
                'type' => Staff::TYPE_HOUSEMAN_OFFICER,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subMonths(6),
                'phone' => '6067890123',
                'is_active' => true,
                'current_rotation' => 'Cardiology',
                'graduation_year' => now()->subYear()->year,
            ],
            
            // Nurses
            [
                'name' => 'Nurse Li Mei',
                'email' => 'li.mei@hospital.com',
                'type' => Staff::TYPE_NURSE,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(4),
                'phone' => '6078901234',
                'is_active' => true,
                'nursing_unit' => 'General Ward',
                'nurse_level' => 'Senior',
            ],
            [
                'name' => 'Nurse David Kumar',
                'email' => 'david.kumar@hospital.com',
                'type' => Staff::TYPE_NURSE,
                'department_id' => $medicalDepartment->id,
                'joining_date' => now()->subYears(1),
                'phone' => '6089012345',
                'is_active' => true,
                'nursing_unit' => 'ICU',
                'nurse_level' => 'Junior',
            ],
        ];

        foreach ($medicalStaff as $staffData) {
            Staff::firstOrCreate(
                ['email' => $staffData['email']],
                $staffData
            );
        }

        $this->command->info('Medical staff created successfully.');
    }
} 