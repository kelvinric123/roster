<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roster;
use App\Models\Department;

class UpdateRosterTypesSeeder extends Seeder
{
    /**
     * Run the database seeds to update roster types to match department roster types.
     */
    public function run(): void
    {
        $rosters = Roster::whereNotNull('department_id')->get();
        
        $count = 0;
        foreach ($rosters as $roster) {
            $department = Department::find($roster->department_id);
            if ($department) {
                $roster->roster_type = $department->roster_type;
                $roster->save();
                $count++;
            }
        }
        
        $this->command->info("Updated roster types for {$count} rosters based on department roster types.");
    }
}
