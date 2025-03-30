<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Staff;

class DepartmentStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        
        foreach ($departments as $department) {
            $this->ensureStaffForDepartment($department, 'specialist_doctor');
            $this->ensureStaffForDepartment($department, 'medical_officer');
            $this->ensureStaffForDepartment($department, 'houseman_officer');
            $this->ensureStaffForDepartment($department, 'nurse');
        }
    }
    
    /**
     * Ensure there is at least one staff of the specified type in the department
     */
    private function ensureStaffForDepartment(Department $department, string $staffType): void
    {
        // Check if department already has staff of this type
        $staffCount = Staff::where('department_id', $department->id)
            ->where('type', $staffType)
            ->count();
        
        if ($staffCount > 0) {
            return; // Department already has staff of this type
        }
        
        // Create a new staff for this department of the required type
        $staffData = [
            'department_id' => $department->id,
            'type' => $staffType,
            'is_active' => true,
            'joining_date' => now()->subMonths(rand(1, 12)),
        ];
        
        // Generate name before other data
        $name = '';
        switch ($staffType) {
            case 'specialist_doctor':
            case 'medical_officer':
            case 'houseman_officer':
                $name = 'Dr. ' . $this->generateMalaysianName();
                break;
            case 'nurse':
                $name = 'Jururawat ' . $this->generateMalaysianName();
                break;
        }
        
        // Check if a staff with similar name already exists in any department
        $similarStaff = Staff::where('name', 'like', '%' . explode(' ', $name)[1] . '%')
            ->first();
        
        if ($similarStaff) {
            // If similar name exists, modify the name slightly
            $nameParts = explode(' ', $name);
            $nameParts[1] = $nameParts[1] . rand(1, 99);
            $name = implode(' ', $nameParts);
        }
        
        switch ($staffType) {
            case 'specialist_doctor':
                $specializations = ['Kardiologi', 'Neurologi', 'Ortopedik', 'Pediatrik', 'Psikiatri'];
                $staffData = array_merge($staffData, [
                    'name' => $name,
                    'email' => $this->generateEmail($name),
                    'phone' => $this->generatePhone(),
                    'specialization' => $specializations[array_rand($specializations)],
                    'qualification' => 'MD, ' . fake()->randomElement(['MRCS', 'FRCS', 'MRCP', 'MMed']),
                    'notes' => fake()->paragraph(2),
                ]);
                break;
                
            case 'medical_officer':
                $departments = ['Kecemasan', 'Pesakit Luar', 'Wad', 'ICU', 'Umum'];
                $staffData = array_merge($staffData, [
                    'name' => $name,
                    'email' => $this->generateEmail($name),
                    'phone' => $this->generatePhone(),
                    'department' => $departments[array_rand($departments)],
                    'notes' => fake()->paragraph(1),
                ]);
                break;
                
            case 'houseman_officer':
                $rotations = ['Perubatan', 'Pembedahan', 'Pediatrik', 'Obstetrik', 'Kecemasan'];
                $staffData = array_merge($staffData, [
                    'name' => $name,
                    'email' => $this->generateEmail($name),
                    'phone' => $this->generatePhone(),
                    'current_rotation' => $rotations[array_rand($rotations)],
                    'graduation_year' => fake()->numberBetween(2020, 2023),
                    'notes' => fake()->paragraph(1),
                ]);
                break;
                
            case 'nurse':
                $units = ['Umum', 'ICU', 'Kecemasan', 'Pediatrik', 'Pembedahan'];
                $levels = ['Jururawat Junior', 'Jururawat Kanan', 'Ketua Jururawat'];
                $staffData = array_merge($staffData, [
                    'name' => $name,
                    'email' => $this->generateEmail($name),
                    'phone' => $this->generatePhone(),
                    'nursing_unit' => $units[array_rand($units)],
                    'nurse_level' => $levels[array_rand($levels)],
                    'notes' => fake()->paragraph(1),
                ]);
                break;
        }
        
        Staff::create($staffData);
    }
    
    /**
     * Generate a Malaysian name
     */
    private function generateMalaysianName(): string
    {
        $malaysianFirstNames = [
            'Ahmad', 'Nur', 'Muhammad', 'Siti', 'Mohd', 
            'Nurul', 'Ibrahim', 'Fatimah', 'Ismail', 'Zulkifli',
            'Tan', 'Wong', 'Lim', 'Rajendran', 'Kumar',
            'Kavitha', 'Saraswathy', 'Muthusamy', 'Govindasamy'
        ];
        
        $malaysianLastNames = [
            'bin Abdullah', 'binti Hassan', 'bin Ibrahim', 'binti Yusof',
            'bin Aziz', 'binti Ismail', 'bin Othman', 'binti Kamil',
            'Wei Ming', 'Chee Keong', 'Mei Ling', 'Siew Mun',
            'a/l Subramaniam', 'a/p Ramasamy', 'a/l Muthusamy', 'a/p Govindasamy'
        ];
        
        return $malaysianFirstNames[array_rand($malaysianFirstNames)] . ' ' . 
               $malaysianLastNames[array_rand($malaysianLastNames)];
    }
    
    /**
     * Generate an email from a name
     */
    private function generateEmail(string $name): string
    {
        $simpleName = str_replace(['Dr. ', 'Jururawat ', 'bin ', 'binti ', 'a/l ', 'a/p '], '', $name);
        $firstName = strtolower(explode(' ', $simpleName)[0]);
        $randomId = rand(100, 999);
        // Add a timestamp suffix to ensure uniqueness
        $timestamp = time();
        return $firstName . $randomId . '_' . $timestamp . '@hospital.gov.my';
    }
    
    /**
     * Generate a Malaysian phone number
     */
    private function generatePhone(): string
    {
        return '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999);
    }
}
