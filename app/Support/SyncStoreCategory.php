<?php

namespace App\Support;

use App\Models\CatalogProductCategory;
use App\Models\CatalogRoom;
use App\Models\Category;
use App\Models\ProjectCategory;
use Illuminate\Support\Str;

/**
 * Keeps the storefront Category tree aligned with catalog masters.
 */
class SyncStoreCategory
{
    public static function syncProject(ProjectCategory $project): Category
    {
        $existing = Category::query()->whereNull('parent_id')->where('name', $project->name)->first();
        $storeStatus = ($project->isActive() && $project->show_on_store)
            ? Category::STATUS_ACTIVE
            : Category::STATUS_INACTIVE;

        return Category::query()->updateOrCreate(
            ['parent_id' => null, 'name' => $project->name],
            [
                'slug' => $existing?->slug ?: self::uniqueSlug(Str::slug($project->name)),
                'status' => $storeStatus,
                'sort_order' => $project->sort_order,
            ],
        );
    }

    public static function syncRoom(CatalogRoom $room): Category
    {
        $room->loadMissing('projectCategory');
        $projectNode = self::syncProject($room->projectCategory);
        $existing = Category::query()->where('parent_id', $projectNode->id)->where('name', $room->name)->first();

        // Room is on the store only if both the project and room allow ecommerce.
        $storeStatus = (
            $room->isActive()
            && $room->show_on_store
            && $room->projectCategory->isActive()
            && $room->projectCategory->show_on_store
        ) ? Category::STATUS_ACTIVE : Category::STATUS_INACTIVE;

        return Category::query()->updateOrCreate(
            ['parent_id' => $projectNode->id, 'name' => $room->name],
            [
                'slug' => $existing?->slug ?: self::uniqueSlug(Str::slug($room->projectCategory->name.' '.$room->name)),
                'status' => $storeStatus,
                'sort_order' => $room->sort_order,
            ],
        );
    }

    public static function syncProductCategory(CatalogProductCategory $leaf): Category
    {
        $leaf->loadMissing('room.projectCategory');
        $roomNode = self::syncRoom($leaf->room);
        $existing = Category::query()->where('parent_id', $roomNode->id)->where('name', $leaf->name)->first();

        $storeStatus = (
            $leaf->isActive()
            && $leaf->room->isActive()
            && $leaf->room->show_on_store
            && $leaf->room->projectCategory->isActive()
            && $leaf->room->projectCategory->show_on_store
        ) ? Category::STATUS_ACTIVE : Category::STATUS_INACTIVE;

        return Category::query()->updateOrCreate(
            ['parent_id' => $roomNode->id, 'name' => $leaf->name],
            [
                'slug' => $existing?->slug ?: self::uniqueSlug(Str::slug($leaf->room->projectCategory->name.' '.$leaf->room->name.' '.$leaf->name)),
                'status' => $storeStatus,
                'sort_order' => $leaf->sort_order,
            ],
        );
    }

    private static function uniqueSlug(string $slug): string
    {
        $candidate = $slug;
        $n = 2;

        while (Category::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$n++;
        }

        return $candidate;
    }
}
