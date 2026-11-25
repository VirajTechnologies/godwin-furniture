<?php

namespace Database\Seeders;

use App\Models\BusinessGroup;
use App\Models\CatalogProductCategory;
use App\Models\CatalogRoom;
use App\Models\DesignType;
use App\Models\ProductModel;
use App\Models\ProjectCategory;
use App\Support\Catalog;
use App\Support\SyncStoreCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogMasterSeeder extends Seeder
{
    /** Internal-only Furniture rooms: seeded for Add Product / branch / agent, hidden on the storefront. */
    private array $adminOnlyRooms = ['Kids Room', 'Study Room', 'Entryway'];

    public function run(): void
    {
        $group = BusinessGroup::query()->updateOrCreate(
            ['name' => (string) config('catalog.business_group', 'Industries')],
            ['status' => BusinessGroup::STATUS_ACTIVE, 'sort_order' => 1],
        );

        $tree = config('catalog.tree', []);
        $projectSort = 1;

        foreach ($tree as $projectName => $rooms) {
            $project = ProjectCategory::query()->updateOrCreate(
                ['business_group_id' => $group->id, 'name' => $projectName],
                [
                    'status' => ProjectCategory::STATUS_ACTIVE,
                    // Interiors is internal-only for now — not on the ecommerce storefront.
                    'show_on_store' => $projectName !== 'Interiors',
                    'sort_order' => $projectSort++,
                ],
            );
            SyncStoreCategory::syncProject($project);

            $roomSort = 1;
            foreach ($rooms as $roomName => $categories) {
                $room = CatalogRoom::query()->updateOrCreate(
                    ['project_category_id' => $project->id, 'name' => $roomName],
                    [
                        'slug' => Str::slug($roomName),
                        'status' => CatalogRoom::STATUS_ACTIVE,
                        // Entire Interiors tree is internal-only; Furniture hides Kids/Study/Entryway.
                        'show_on_store' => $projectName !== 'Interiors'
                            && ! in_array($roomName, $this->adminOnlyRooms, true),
                        'sort_order' => $roomSort++,
                    ],
                );
                SyncStoreCategory::syncRoom($room);

                $categorySort = 1;
                foreach ($categories as $categoryName) {
                    $leaf = CatalogProductCategory::query()->updateOrCreate(
                        ['catalog_room_id' => $room->id, 'name' => $categoryName],
                        [
                            'slug' => Str::slug($categoryName),
                            'status' => CatalogProductCategory::STATUS_ACTIVE,
                            'sort_order' => $categorySort++,
                        ],
                    );
                    SyncStoreCategory::syncProductCategory($leaf);

                    $typeSort = 1;
                    foreach (Catalog::typesForFromConfig($categoryName) as $typeName) {
                        DesignType::query()->updateOrCreate(
                            ['catalog_product_category_id' => $leaf->id, 'name' => $typeName],
                            ['status' => DesignType::STATUS_ACTIVE, 'sort_order' => $typeSort++],
                        );
                    }

                    $modelSort = 1;
                    foreach (Catalog::modelsForFromConfig($categoryName) as $modelName) {
                        ProductModel::query()->updateOrCreate(
                            ['catalog_product_category_id' => $leaf->id, 'name' => $modelName],
                            ['status' => ProductModel::STATUS_ACTIVE, 'sort_order' => $modelSort++],
                        );
                    }
                }
            }
        }
    }
}
