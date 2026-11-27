<?php

namespace Database\Seeders;

use App\Models\CatalogConfigOption;
use App\Models\ProductColour;
use App\Models\ProductFinish;
use App\Models\ProductUpholstery;
use App\Support\Catalog;
use Illuminate\Database\Seeder;

class CatalogConfigSeeder extends Seeder
{
    public function run(): void
    {
        $colourSort = 1;
        foreach (config('catalog.colours', []) as $name => $hex) {
            ProductColour::query()->updateOrCreate(
                ['name' => $name],
                [
                    'hex' => $hex,
                    'status' => ProductColour::STATUS_ACTIVE,
                    'sort_order' => $colourSort++,
                ],
            );
        }

        $finishSort = 1;
        foreach (config('catalog.finishes', []) as $name) {
            ProductFinish::query()->updateOrCreate(
                ['name' => $name],
                ['status' => ProductFinish::STATUS_ACTIVE, 'sort_order' => $finishSort++],
            );
        }

        $upholsterySort = 1;
        foreach (config('catalog.upholstery', []) as $name) {
            ProductUpholstery::query()->updateOrCreate(
                ['name' => $name],
                ['status' => ProductUpholstery::STATUS_ACTIVE, 'sort_order' => $upholsterySort++],
            );
        }

        foreach (config('catalog.families', []) as $familyKey => $family) {
            foreach ($family['fields'] ?? [] as $index => $field) {
                if (($field['type'] ?? 'n') !== 's') {
                    continue;
                }

                $fieldKey = Catalog::fieldKeyFromLabel((string) $field['label']);
                $optionSort = 1;

                foreach ($field['options'] ?? [] as $optionName) {
                    CatalogConfigOption::query()->updateOrCreate(
                        [
                            'family_key' => $familyKey,
                            'field_key' => $fieldKey,
                            'name' => $optionName,
                        ],
                        [
                            'status' => CatalogConfigOption::STATUS_ACTIVE,
                            'sort_order' => $optionSort++,
                        ],
                    );
                }
            }
        }
    }
}
