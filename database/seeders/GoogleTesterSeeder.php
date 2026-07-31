<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cabang;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeItem;
use App\Models\JurnalLifeCheck;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GoogleTesterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or update tester user
        $email = 'playstore_tester@studycenter.nanoprojectdevindonesia.com';
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Google Play Reviewer',
                'password' => Hash::make('Password123!'),
                'username' => 'googletester',
                'active' => true,
            ]
        );

        // Assign 'student' role if using spatie/laravel-permission
        if (! $user->hasRole('student')) {
            $user->assignRole('student');
        }

        // Make sure we have a branch (cabang)
        $cabang = Cabang::first();
        if ($cabang) {
            $user->cabang_id = $cabang->id;
            $user->save();
        }

        $this->command->info("Tester account created: {$email} / Password123!");

        // 2. Populate journal data for the last 5 days
        $lifeItems = JurnalLifeItem::all();
        
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::today()->subDays($i);
            $weekKey = JurnalWeek::weekKeyFor($date);
            
            // Create Journal Entry
            $entry = JurnalEntry::firstOrCreate([
                'student_id' => $user->id,
                'tanggal' => $date->toDateString(),
            ]);

            // Fake some ticked boxes for Bible Reading (PL/PB)
            $entry->update([
                'pl_checked' => true,
                'pb_checked' => true,
            ]);

            // For Hafal Ayat, just put it on the first day of the loop (Today or Yesterday)
            if ($i === 0) {
                // To avoid the verse week key collision issue, check if we already have it
                $weekEntry = JurnalEntry::where('student_id', $user->id)
                    ->where('verse_week_key', $weekKey)
                    ->first();
                    
                if ($weekEntry) {
                    $weekEntry->update(['verse_ref' => 'Yohanes 3:16']);
                } else {
                    $entry->update(['verse_week_key' => $weekKey, 'verse_ref' => 'Yohanes 3:16']);
                }
            }

            // Fake Life Items (Checkboxes)
            foreach ($lifeItems as $item) {
                // Randomly check some items
                if (rand(0, 1) === 1) {
                    JurnalLifeCheck::updateOrCreate(
                        [
                            'student_id' => $user->id,
                            'life_item_id' => $item->id,
                            'tanggal' => $date->toDateString()
                        ],
                        ['checked' => true]
                    );
                }
            }
        }

        $this->command->info("Populated dummy journal data for the last 5 days.");
    }
}
