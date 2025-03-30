<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all department IDs
        $departmentIds = Department::pluck('id')->toArray();
        
        // Specialist Doctors
        $this->createSpecialistDoctors($departmentIds, $faker);
        
        // Medical Officers
        $this->createMedicalOfficers($departmentIds, $faker);
        
        // Houseman Officers
        $this->createHousemanOfficers($departmentIds, $faker);
        
        // Nurses
        $this->createNurses($departmentIds, $faker);
        
        // Create additional staff specifically for Internal Medicine Department (ID: 1)
        $this->createInternalMedicineStaff($faker);
        
        // Create admin staff
        $this->createAdminStaff();
        
        // Create user accounts for all staff
        $this->createUserAccounts();
        
        $this->command->info('Created user accounts for all staff with default password: qmed.asia');
    }

    private function createSpecialistDoctors($departmentIds, $faker)
    {
        $specializations = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'Psychiatry'];
        $malaysianDoctorNames = [
            'Dr. Ahmad bin Ibrahim', 
            'Dr. Nurul Izzah binti Hassan', 
            'Dr. Tan Wei Ming', 
            'Dr. Rajendran a/l Subramaniam', 
            'Dr. Siti Fatimah binti Abdullah'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $name = $malaysianDoctorNames[$i];
            $email = strtolower(explode(' ', str_replace(['Dr. ', 'bin ', 'binti ', 'a/l '], '', $name))[0]) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'specialist_doctor',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'specialization' => $specializations[$i],
                'qualification' => 'MD, ' . $faker->randomElement(['MRCS', 'FRCS', 'MRCP', 'MMed']),
                'joining_date' => $faker->dateTimeBetween('-5 years', '-6 months'),
                'is_active' => true,
                'notes' => $faker->paragraph(2),
            ]);
        }
    }

    private function createMedicalOfficers($departmentIds, $faker)
    {
        $departments = ['Emergency', 'Outpatient', 'Ward', 'ICU', 'General'];
        $malaysianMONames = [
            'Dr. Amirah binti Zulkifli', 
            'Dr. Lim Chee Keong', 
            'Dr. Muhammad Farid bin Othman', 
            'Dr. Kavitha a/p Ramasamy', 
            'Dr. Zulkarnain bin Mahmud'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $name = $malaysianMONames[$i];
            $email = strtolower(explode(' ', str_replace(['Dr. ', 'bin ', 'binti ', 'a/p '], '', $name))[0]) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'medical_officer',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'department' => $departments[$i],
                'joining_date' => $faker->dateTimeBetween('-3 years', '-6 months'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
    }

    private function createHousemanOfficers($departmentIds, $faker)
    {
        $rotations = ['Medicine', 'Surgery', 'Pediatrics', 'Obstetrics', 'Emergency'];
        $malaysianHONames = [
            'Dr. Firdaus bin Aziz', 
            'Dr. Leong Mei Ling', 
            'Dr. Nur Syafiqah binti Kamil', 
            'Dr. Karthik a/l Muthusamy', 
            'Dr. Hafizah binti Ismail'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $name = $malaysianHONames[$i];
            $email = strtolower(explode(' ', str_replace(['Dr. ', 'bin ', 'binti ', 'a/l '], '', $name))[0]) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'houseman_officer',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'current_rotation' => $rotations[$i],
                'graduation_year' => $faker->numberBetween(2020, 2023),
                'joining_date' => $faker->dateTimeBetween('-1 year', '-1 month'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
    }

    private function createNurses($departmentIds, $faker)
    {
        $units = ['General', 'ICU', 'Emergency', 'Pediatrics', 'Surgery'];
        $levels = ['Junior Nurse', 'Senior Nurse', 'Head Nurse', 'Junior Nurse', 'Senior Nurse'];
        $malaysianNurseNames = [
            'Jururawat Norazlina binti Mohd Razali', 
            'Jururawat Wong Siew Mun', 
            'Jururawat Saraswathy a/p Govindasamy', 
            'Jururawat Noor Aini binti Yusof', 
            'Jururawat Hanis binti Abdul Rahman'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $name = $malaysianNurseNames[$i];
            $simpleName = str_replace(['Jururawat ', 'bin ', 'binti ', 'a/p '], '', $name);
            $email = strtolower(explode(' ', $simpleName)[0]) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'nurse',
                'department_id' => $departmentIds[array_rand($departmentIds)],
                'nursing_unit' => $units[$i],
                'nurse_level' => $levels[$i],
                'joining_date' => $faker->dateTimeBetween('-4 years', '-3 months'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
    }
    
    /**
     * Create additional staff specifically for Internal Medicine Department (ID: 1)
     */
    private function createInternalMedicineStaff($faker)
    {
        // Internal Medicine specific specializations
        $internalMedicineSpecializations = [
            'Gastroenterology', 
            'Pulmonology', 
            'Endocrinology', 
            'Hematology', 
            'Infectious Disease',
            'Nephrology',
            'Rheumatology'
        ];
        
        // 1. Add specialist doctors to Internal Medicine
        $specialistDoctors = [
            'Dr. Haris bin Abdullah' => 'Gastroenterology',
            'Dr. Lim Sze Han' => 'Pulmonology',
            'Dr. Anita binti Razak' => 'Endocrinology',
            'Dr. Ravi Kumar a/l Sundaram' => 'Hematology', 
            'Dr. Amalina binti Mohd Noor' => 'Infectious Disease',
            'Dr. Chong Wei Jian' => 'Nephrology',
            'Dr. Nurul Ashikin binti Hamid' => 'Rheumatology'
        ];
        
        foreach ($specialistDoctors as $name => $specialization) {
            $simpleName = str_replace(['Dr. ', 'bin ', 'binti ', 'a/l '], '', $name);
            $firstName = explode(' ', $simpleName)[0];
            $email = strtolower($firstName) . '.' . $faker->randomNumber(3) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'specialist_doctor',
                'department_id' => 1, // Internal Medicine Department
                'specialization' => $specialization,
                'qualification' => 'MD, ' . $faker->randomElement(['MRCP', 'MMed(Int Med)', 'Fellowship', 'FRCP']),
                'joining_date' => $faker->dateTimeBetween('-8 years', '-1 year'),
                'is_active' => true,
                'notes' => $faker->paragraph(2),
            ]);
        }
        
        // 2. Add medical officers to Internal Medicine
        $medicalOfficers = [
            'Dr. Aizat bin Kamal', 
            'Dr. Lee Mei Chen', 
            'Dr. Nur Hidayah binti Roslan', 
            'Dr. Kumaravel a/l Raju', 
            'Dr. Zainab binti Omar',
            'Dr. Daniel Thiam Liang',
            'Dr. Farahana binti Azman',
            'Dr. Voon Jia Hui',
            'Dr. Mohd Hisham bin Ismail'
        ];
        
        $departments = ['Ward A', 'Ward B', 'Ward C', 'Outpatient', 'Emergency', 'Day Care', 'Consultation', 'Triage', 'General'];
        
        foreach ($medicalOfficers as $index => $name) {
            $simpleName = str_replace(['Dr. ', 'bin ', 'binti ', 'a/l '], '', $name);
            $firstName = explode(' ', $simpleName)[0];
            $email = strtolower($firstName) . '.' . $faker->randomNumber(3) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'medical_officer',
                'department_id' => 1, // Internal Medicine Department
                'department' => $departments[$index],
                'joining_date' => $faker->dateTimeBetween('-4 years', '-2 months'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
        
        // 3. Add houseman officers to Internal Medicine
        $housemanOfficers = [
            'Dr. Azreen binti Azhar', 
            'Dr. Teo Jun Wei', 
            'Dr. Nur Athirah binti Salleh', 
            'Dr. Vikneswaran a/l Mohan', 
            'Dr. Aishah binti Zamri',
            'Dr. Wong Kar Meng'
        ];
        
        $rotations = ['Ward', 'Clinic', 'Consultation', 'Emergency', 'Outpatient', 'Diagnostic'];
        
        foreach ($housemanOfficers as $index => $name) {
            $simpleName = str_replace(['Dr. ', 'bin ', 'binti ', 'a/l '], '', $name);
            $firstName = explode(' ', $simpleName)[0];
            $email = strtolower($firstName) . '.' . $faker->randomNumber(3) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'houseman_officer',
                'department_id' => 1, // Internal Medicine Department
                'current_rotation' => $rotations[$index],
                'graduation_year' => $faker->numberBetween(2021, 2023),
                'joining_date' => $faker->dateTimeBetween('-10 months', '-1 month'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
        
        // 4. Add nurses to Internal Medicine
        $nurses = [
            'Jururawat Siti Aminah binti Hassan', 
            'Jururawat Tan Li Ping', 
            'Jururawat Jayanthi a/p Kumaran', 
            'Jururawat Nurliza binti Ahmad', 
            'Jururawat Fauziah binti Mohd Salleh',
            'Jururawat Emily Wong Sze Mun',
            'Jururawat Kamariah binti Zulkifli',
            'Jururawat Vimala a/p Subramaniam',
            'Jururawat Aina binti Baharuddin',
            'Jururawat Leong Mei Yan'
        ];
        
        $units = ['Ward A', 'Ward B', 'Ward C', 'Outpatient', 'Emergency', 'Day Care', 'Consultation', 'Triage', 'General', 'Diagnostic'];
        $levels = ['Junior Nurse', 'Senior Nurse', 'Head Nurse', 'Junior Nurse', 'Senior Nurse', 'Junior Nurse', 'Senior Nurse', 'Head Nurse', 'Junior Nurse', 'Senior Nurse'];
        
        foreach ($nurses as $index => $name) {
            $simpleName = str_replace(['Jururawat ', 'bin ', 'binti ', 'a/p '], '', $name);
            $firstName = explode(' ', $simpleName)[0];
            $email = strtolower($firstName) . '.' . $faker->randomNumber(3) . '@hospital.gov.my';
            
            Staff::create([
                'name' => $name,
                'email' => $email,
                'phone' => '01' . rand(0, 1) . '-' . rand(1000, 9999) . rand(1000, 9999),
                'type' => 'nurse',
                'department_id' => 1, // Internal Medicine Department
                'nursing_unit' => $units[$index],
                'nurse_level' => $levels[$index],
                'joining_date' => $faker->dateTimeBetween('-5 years', '-1 month'),
                'is_active' => true,
                'notes' => $faker->paragraph(1),
            ]);
        }
    }
    
    /**
     * Create admin staff
     */
    private function createAdminStaff()
    {
        Staff::create([
            'name' => 'System Administrator',
            'email' => 'admin@qmed.asia',
            'phone' => '010-12345678',
            'type' => 'admin',
            'department_id' => 1, // Main department ID
            'joining_date' => Carbon::now()->subYears(1),
            'is_active' => true,
            'notes' => 'System administrator account',
        ]);
        
        $this->command->info('Created admin staff');
    }
    
    /**
     * Create user accounts for all staff with default password
     */
    private function createUserAccounts()
    {
        $defaultPassword = 'qmed.asia';
        $staffMembers = Staff::whereDoesntHave('user')->get();
        
        foreach ($staffMembers as $staff) {
            User::create([
                'name' => $staff->name,
                'email' => $staff->email,
                'password' => Hash::make($defaultPassword),
                'staff_id' => $staff->id,
                'role' => $staff->type,
            ]);
        }
        
        $this->command->info("Created {$staffMembers->count()} user accounts");
    }
}
