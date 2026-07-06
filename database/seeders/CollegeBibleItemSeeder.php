<?php

namespace Database\Seeders;

use App\Models\CollegeBibleItem;
use App\Models\CollegeConfig;
use Illuminate\Database\Seeder;

class CollegeBibleItemSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(base_path('jadwal_pembacaan_alkitab.json'));
        $items = json_decode($json, true);

        foreach ($items as $item) {
            CollegeBibleItem::updateOrCreate(
                ['day_no' => $item['no']],
                ['pl_text' => $item['PL'], 'pb_text' => $item['PB']]
            );
        }

        CollegeConfig::updateOrCreate(
            ['id' => 1],
            [
                'anchor_day_no'   => 187,
                'anchor_date'     => '2026-07-06',
                'form_open_time'  => '06:00:00',
                'form_close_time' => '23:59:00',
            ]
        );

        $this->command->info('College bible items seeded: ' . count($items) . ' rows');
    }
}
