<?php

namespace App\Services;

use App\Models\JurnalLifeItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class JurnalSetupService
{
    private const ROLE_CATEGORIES = [
        'student'              => ['kerohanian', 'pendidikan', 'karakter'],
        'scholarship_teenager' => ['kerohanian', 'pendidikan', 'karakter'],
        'college'              => ['kerohanian', 'pendidikan_kuliah', 'karakter', 'pendidikan'],
    ];

    /**
     * Assign default active template items to a user based on their role.
     * Skips items already assigned (insertOrIgnore on composite PK).
     */
    public function setupForRole(User $user, string $role): void
    {
        $categories = self::ROLE_CATEGORIES[$role] ?? null;
        if ($categories === null) {
            return;
        }

        $items = JurnalLifeItem::whereNull('student_id')
            ->where('is_active', true)
            ->whereIn('kategori', $categories)
            ->pluck('id');

        if ($items->isEmpty()) {
            return;
        }

        $now  = now();
        $rows = $items->map(fn($itemId) => [
            'student_id'   => $user->id,
            'life_item_id' => $itemId,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->all();

        DB::table('jurnal_student_life_items')->insertOrIgnore($rows);
    }

    /**
     * Reset jurnal items for a user to role defaults.
     * Clears existing assignments first, then re-assigns defaults.
     */
    public function resetToDefaults(User $user, string $role): void
    {
        DB::table('jurnal_student_life_items')->where('student_id', $user->id)->delete();
        $this->setupForRole($user, $role);
    }
}
