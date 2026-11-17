<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorldGeoSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $file = storage_path('app/geo/countries+states+cities.json');
        if (!is_file($file)) {
            throw new \RuntimeException('Missing ' . $file);
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            throw new \RuntimeException('Could not read JSON. Re-download the file.');
        }

        $now = now();
        $nCountries = $nStates = $nCities = 0;

        foreach ($data as $c) {
            DB::table('countries')->insertOrIgnore([
                'name' => mb_substr($c['name'], 0, 255),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $countryId = DB::table('countries')->where('name', $c['name'])->value('id');
            if (!$countryId) continue;
            $nCountries++;

            $states = $c['states'] ?? [];

            $rows = [];
            foreach ($states as $s) {
                $rows[] = [
                    'country_id' => $countryId,
                    'name' => mb_substr($s['name'], 0, 255),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('states')->insertOrIgnore($chunk);
            }
            $nStates += count($rows);

            $stateIds = DB::table('states')->where('country_id', $countryId)->pluck('id', 'name');

            foreach ($states as $s) {
                $stateId = $stateIds[$s['name']] ?? null;
                if (!$stateId) continue;

                $rows = [];
                foreach ($s['cities'] ?? [] as $city) {
                    $rows[] = [
                        'state_id' => $stateId,
                        'name' => mb_substr($city['name'], 0, 255),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach (array_chunk($rows, 1000) as $chunk) {
                    DB::table('districts')->insertOrIgnore($chunk);
                }
                $nCities += count($rows);
            }

            $this->command?->info("{$c['name']} done");
        }

        $this->command?->info("Countries: {$nCountries}, States: {$nStates}, Cities: {$nCities}");
    }
}