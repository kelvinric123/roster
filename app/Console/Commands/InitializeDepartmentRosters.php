<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;

class InitializeDepartmentRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:init-department-rosters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize default staff type roster settings for all departments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $departments = Department::all();
        
        $this->info("Initializing staff type roster settings for {$departments->count()} departments...");
        
        $count = 0;
        
        foreach ($departments as $department) {
            $this->info("Processing department: {$department->name}");
            $department->initializeStaffTypeRosters();
            $count++;
            
            // Add a progress bar or percentage
            $this->output->write("\r[" . str_repeat("=", $count) . str_repeat(" ", $departments->count() - $count) . "] " . 
                round(($count / $departments->count()) * 100) . "%");
        }
        
        $this->newLine();
        $this->info("Staff type roster settings initialized successfully for {$count} departments.");
        
        return 0;
    }
} 