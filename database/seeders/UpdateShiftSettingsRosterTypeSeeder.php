<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RosterShiftSetting;

class UpdateShiftSettingsRosterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Standard shift types get 'shift' roster type
        $shiftCount = RosterShiftSetting::whereIn('shift_type', ['morning', 'evening', 'night'])
            ->update(['roster_type' => 'shift']);
            
        // Oncall and standby get 'oncall' roster type
        $oncallCount = RosterShiftSetting::whereIn('shift_type', ['oncall', 'standby'])
            ->update(['roster_type' => 'oncall']);
            
        // Any other types default to 'shift'
        $otherCount = RosterShiftSetting::whereNotIn('shift_type', ['morning', 'evening', 'night', 'oncall', 'standby'])
            ->update(['roster_type' => 'shift']);
            
        $this->command->info("Updated roster_type for roster shift settings: {$shiftCount} shift, {$oncallCount} oncall, {$otherCount} other.");
    }
}
