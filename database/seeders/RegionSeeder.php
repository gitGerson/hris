<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class RegionSeeder extends Seeder
{
    /**
     * Public dataset of Indonesian provinces and regencies/cities.
     */
    protected const SOURCE_URL = 'https://wilayah.id/api';

    public function run(): void
    {
        $regions = $this->regions();

        if ($regions === null) {
            $this->command?->warn('Region dataset unavailable, skipping. Provinces and cities were not seeded.');

            return;
        }

        foreach ($regions['provinces'] as $province) {
            Province::updateOrCreate(
                ['code' => $province['code']],
                ['name' => $province['name']],
            );
        }

        $provinceIds = Province::pluck('id', 'code');
        $now = now();

        collect($regions['cities'])
            ->filter(fn (array $city): bool => isset($provinceIds[$city['province_code']]))
            ->map(fn (array $city): array => [
                'province_id' => $provinceIds[$city['province_code']],
                'code' => $city['code'],
                'name' => $city['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk(500)
            /** upsert keeps this to a few queries instead of ~500 round trips. */
            ->each(fn ($chunk) => City::upsert($chunk->all(), ['code'], ['province_id', 'name', 'updated_at']));

        $this->command?->info('Seeded '.count($regions['provinces']).' provinces and '.count($regions['cities']).' cities.');
    }

    /**
     * Fetched once, then cached on disk, so later runs and CI do not need the network.
     */
    protected function cachePath(): string
    {
        return database_path('data/regions.json');
    }

    /**
     * @return array{provinces: array<int, array{code: string, name: string}>, cities: array<int, array{code: string, name: string, province_code: string}>}|null
     */
    protected function regions(): ?array
    {
        if (File::exists($this->cachePath())) {
            return json_decode(File::get($this->cachePath()), true);
        }

        $regions = $this->fetch();

        if ($regions !== null) {
            File::ensureDirectoryExists(dirname($this->cachePath()));
            File::put($this->cachePath(), json_encode($regions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $regions;
    }

    /**
     * One call for the province list, then one per province for its regencies.
     *
     * @return array{provinces: array<int, array{code: string, name: string}>, cities: array<int, array{code: string, name: string, province_code: string}>}|null
     */
    protected function fetch(): ?array
    {
        try {
            $this->command?->info('Fetching region dataset from '.self::SOURCE_URL.' (one time only)...');

            $provinces = Http::timeout(30)->get(self::SOURCE_URL.'/provinces.json')->throw()->json('data');
            $cities = [];

            foreach ($provinces as $province) {
                $regencies = Http::timeout(30)
                    ->get(self::SOURCE_URL.'/regencies/'.$province['code'].'.json')
                    ->throw()
                    ->json('data');

                foreach ($regencies as $regency) {
                    $cities[] = [
                        'code' => $regency['code'],
                        'name' => $regency['name'],
                        'province_code' => $province['code'],
                    ];
                }
            }

            return ['provinces' => $provinces, 'cities' => $cities];
        } catch (Throwable $e) {
            $this->command?->warn('Could not fetch regions: '.$e->getMessage());

            return null;
        }
    }
}
