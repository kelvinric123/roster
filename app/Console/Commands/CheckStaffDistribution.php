<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Staff;

class CheckStaffDistribution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:check-staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check staff distribution by department and type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $departments = Department::all();
        
        $this->info("Staff Distribution by Department:");
        $this->newLine();
        
        $headers = ['Department', 'Specialist', 'Medical Officer', 'Houseman', 'Nurse', 'Total'];
        $rows = [];
        
        $totalSpecialists = 0;
        $totalMOs = 0;
        $totalHousemen = 0;
        $totalNurses = 0;
        $grandTotal = 0;
        
        foreach ($departments as $department) {
            $specialists = Staff::where('department_id', $department->id)
                               ->where('type', 'specialist_doctor')
                               ->count();
                               
            $medicalOfficers = Staff::where('department_id', $department->id)
                                   ->where('type', 'medical_officer')
                                   ->count();
                                   
            $housemen = Staff::where('department_id', $department->id)
                            ->where('type', 'houseman_officer')
                            ->count();
                            
            $nurses = Staff::where('department_id', $department->id)
                          ->where('type', 'nurse')
                          ->count();
                          
            $total = $specialists + $medicalOfficers + $housemen + $nurses;
            
            $totalSpecialists += $specialists;
            $totalMOs += $medicalOfficers;
            $totalHousemen += $housemen;
            $totalNurses += $nurses;
            $grandTotal += $total;
            
            $rows[] = [
                $department->name,
                $specialists,
                $medicalOfficers,
                $housemen,
                $nurses,
                $total
            ];
        }
        
        // Add totals row
        $rows[] = ['TOTAL', $totalSpecialists, $totalMOs, $totalHousemen, $totalNurses, $grandTotal];
        
        $this->table($headers, $rows);
        
        return 0;
    }
} 