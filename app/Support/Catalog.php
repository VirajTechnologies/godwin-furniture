<?php

namespace App\Support;

use App\Models\CatalogConfigOption;
use App\Models\CatalogProductCategory;
use App\Models\Category;
use App\Models\DesignType;
use App\Models\ProductColour;
use App\Models\ProductFinish;
use App\Models\ProductModel;
use App\Models\ProductUpholstery;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Product Manager taxonomy helpers — prefers catalog master tables, falls back to config.
 */
class Catalog
{
    /**
     * @return array<string, array<string, list<string>>>
     */
    public static function tree(): array
    {
        if (! self::mastersReady()) {
            return config('catalog.tree', []);
        }

        $tree = [];

        ProjectCategory::query()
            ->where('status', ProjectCategory::STATUS_ACTIVE)
            ->with(['rooms' => fn ($q) => $q
                ->where('status', \App\Models\CatalogRoom::STATUS_ACTIVE)
                ->with(['productCategories' => fn ($c) => $c->where('status', CatalogProductCategory::STATUS_ACTIVE)])])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (ProjectCategory $project) use (&$tree): void {
                $tree[$project->name] = [];
                foreach ($project->rooms as $room) {
                    $tree[$project->name][$room->name] = $room->productCategories->pluck('name')->all();
                }
            });

        return $tree !== [] ? $tree : config('catalog.tree', []);
    }

    /**
     * @return list<string>
     */
    public static function projects(): array
    {
        return array_keys(self::tree());
    }

    /**
     * @return list<string>
     */
    public static function rooms(string $project): array
    {
        return array_keys(self::tree()[$project] ?? []);
    }

    /**
     * @return list<string>
     */
    public static function categories(string $project, string $room): array
    {
        return self::tree()[$project][$room] ?? [];
    }

    public static function pathExists(string $project, string $room, string $category): bool
    {
        return in_array($category, self::categories($project, $room), true);
    }

    /**
     * @return list<string>
     */
    public static function typesFor(string $category): array
    {
        if (self::mastersReady()) {
            $leaf = CatalogProductCategory::query()
                ->where('name', $category)
                ->where('status', CatalogProductCategory::STATUS_ACTIVE)
                ->first();

            if ($leaf) {
                $types = DesignType::query()
                    ->where('catalog_product_category_id', $leaf->id)
                    ->where('status', DesignType::STATUS_ACTIVE)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();

                if ($types !== []) {
                    return $types;
                }
            }
        }

        return self::typesForFromConfig($category);
    }

    /**
     * @return list<string>
     */
    public static function typesForFromConfig(string $category): array
    {
        foreach (config('catalog.types', []) as $row) {
            if (preg_match($row['pattern'], $category) === 1) {
                return $row['types'];
            }
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public static function modelsFor(string $category): array
    {
        if (self::mastersReady()) {
            $leaf = CatalogProductCategory::query()
                ->where('name', $category)
                ->where('status', CatalogProductCategory::STATUS_ACTIVE)
                ->first();

            if ($leaf) {
                $models = ProductModel::query()
                    ->where('catalog_product_category_id', $leaf->id)
                    ->where('status', ProductModel::STATUS_ACTIVE)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();

                if ($models !== []) {
                    return $models;
                }
            }
        }

        return self::modelsForFromConfig($category);
    }

    /**
     * @return list<string>
     */
    public static function modelsForFromConfig(string $category): array
    {
        $names = config('catalog.model_names', []);

        if ($names === []) {
            return [];
        }

        $hash = array_sum(array_map('ord', mb_str_split($category)));
        $suffix = preg_replace('/ Size$/', '', $category);
        $models = [];

        for ($i = 0; $i < 8; $i++) {
            $models[] = $names[($hash + $i * 3) % count($names)].' '.$suffix;
        }

        return $models;
    }

    public static function family(string $project, string $category): string
    {
        if ($project === 'Interiors') {
            return config('catalog.interiors_family', 'int');
        }

        foreach (config('catalog.family_patterns', []) as $family => $pattern) {
            if (preg_match($pattern, $category) === 1) {
                return $family;
            }
        }

        return config('catalog.default_family', 'store');
    }

    /**
     * @return array{label: string, fields: list<array<string, mixed>>}
     */
    public static function familyConfig(string $family): array
    {
        $config = config('catalog.families.'.$family) ?? config('catalog.families.'.config('catalog.default_family', 'store'));
        $config = is_array($config) ? $config : ['label' => $family, 'fields' => []];

        if (! self::configMastersReady()) {
            return $config;
        }

        $fields = [];
        foreach ($config['fields'] ?? [] as $field) {
            if (($field['type'] ?? 'n') === 's') {
                $fieldKey = self::fieldKeyFromLabel((string) ($field['label'] ?? ''));
                $options = CatalogConfigOption::query()
                    ->where('family_key', $family)
                    ->where('field_key', $fieldKey)
                    ->where('status', CatalogConfigOption::STATUS_ACTIVE)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();

                if ($options !== []) {
                    $field['options'] = $options;
                }
            }
            $fields[] = $field;
        }

        $config['fields'] = $fields;

        return $config;
    }

    public static function fieldKeyFromLabel(string $label): string
    {
        return Str::slug($label);
    }

    /**
     * Select fields for one family: field_key => label.
     *
     * @return array<string, string>
     */
    public static function selectFieldsForFamily(string $family): array
    {
        $fields = [];
        foreach (config('catalog.families.'.$family.'.fields', []) as $field) {
            if (($field['type'] ?? 'n') !== 's') {
                continue;
            }
            $label = (string) ($field['label'] ?? '');
            $fields[self::fieldKeyFromLabel($label)] = $label;
        }

        return $fields;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function selectFieldsByFamily(): array
    {
        $map = [];
        foreach (array_keys(config('catalog.families', [])) as $family) {
            $map[$family] = self::selectFieldsForFamily($family);
        }

        return $map;
    }

    public static function usesUpholstery(?string $family): bool
    {
        return in_array($family, config('catalog.upholstery_families', []), true);
    }

    /**
     * @return array<string, string>
     */
    public static function colours(): array
    {
        if (self::configMastersReady()) {
            $rows = ProductColour::query()
                ->where('status', ProductColour::STATUS_ACTIVE)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('hex', 'name')
                ->all();

            if ($rows !== []) {
                return $rows;
            }
        }

        return config('catalog.colours', []);
    }

    public static function colourHex(?string $name): string
    {
        return self::colours()[$name] ?? '#888888';
    }

    /**
     * @return list<string>
     */
    public static function finishes(): array
    {
        if (self::configMastersReady()) {
            $rows = ProductFinish::query()
                ->where('status', ProductFinish::STATUS_ACTIVE)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();

            if ($rows !== []) {
                return $rows;
            }
        }

        return config('catalog.finishes', []);
    }

    /**
     * @return list<string>
     */
    public static function upholsteries(): array
    {
        if (self::configMastersReady()) {
            $rows = ProductUpholstery::query()
                ->where('status', ProductUpholstery::STATUS_ACTIVE)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();

            if ($rows !== []) {
                return $rows;
            }
        }

        return config('catalog.upholstery', []);
    }

    public static function skuFor(string $code, string $colour, string $finish, ?string $upholstery = null): string
    {
        $suffix = strtoupper(mb_substr(preg_replace('/[^A-Za-z]/', '', $colour), 0, 3))
            .strtoupper(mb_substr($finish, 0, 1))
            .($upholstery ? strtoupper(mb_substr($upholstery, 0, 2)) : '');

        return strtoupper($code).'-'.$suffix;
    }

    public static function productCode(string $project, string $room, string $category, int $sequence): string
    {
        $prefix = $project === 'Interiors' ? 'I' : 'F';

        return sprintf(
            'GI-%s-%s-%s-%04d',
            $prefix,
            strtoupper(mb_substr($room, 0, 2)),
            strtoupper(mb_substr(preg_replace('/[^A-Za-z]/', '', $category), 0, 3)),
            $sequence,
        );
    }

    /**
     * Find or create the project → room → product category chain and return the leaf.
     */
    public static function leafCategory(string $project, string $room, string $category): Category
    {
        $projectIndex = array_search($project, self::projects(), true);
        $projectIndex = $projectIndex === false ? 0 : $projectIndex;

        $projectModel = Category::query()->firstOrCreate(
            ['parent_id' => null, 'name' => $project],
            ['slug' => Str::slug($project), 'status' => Category::STATUS_ACTIVE, 'sort_order' => $projectIndex + 1],
        );

        $roomIndex = array_search($room, self::rooms($project), true);
        $roomModel = Category::query()->firstOrCreate(
            ['parent_id' => $projectModel->id, 'name' => $room],
            [
                'slug' => self::uniqueSlug(Str::slug($project.' '.$room)),
                'status' => Category::STATUS_ACTIVE,
                'sort_order' => ($roomIndex === false ? 0 : $roomIndex) + 1,
            ],
        );

        $categoryIndex = array_search($category, self::categories($project, $room), true);

        return Category::query()->firstOrCreate(
            ['parent_id' => $roomModel->id, 'name' => $category],
            [
                'slug' => self::uniqueSlug(Str::slug($project.' '.$room.' '.$category)),
                'status' => Category::STATUS_ACTIVE,
                'sort_order' => ($categoryIndex === false ? 0 : $categoryIndex) + 1,
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

    private static function mastersReady(): bool
    {
        try {
            return Schema::hasTable('project_categories')
                && ProjectCategory::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Payload consumed by the admin Add/Edit Product dropdown script.
     *
     * @return array<string, mixed>
     */
    public static function clientConfig(): array
    {
        $types = [];
        $models = [];
        $families = [];

        foreach (self::tree() as $project => $rooms) {
            foreach ($rooms as $categories) {
                foreach ($categories as $category) {
                    $types[$category] ??= self::typesFor($category);
                    $models[$category] ??= self::modelsFor($category);
                    $families[$project][$category] = self::family($project, $category);
                }
            }
        }

        $businessGroup = config('catalog.business_group');
        if (self::mastersReady()) {
            $businessGroup = \App\Models\BusinessGroup::query()
                ->where('status', \App\Models\BusinessGroup::STATUS_ACTIVE)
                ->orderBy('sort_order')
                ->value('name') ?: $businessGroup;
        }

        return [
            'businessGroup' => $businessGroup,
            'tree' => self::tree(),
            'types' => $types,
            'models' => $models,
            'families' => $families,
            'familyFields' => collect(config('catalog.families'))->map(function ($family, $key) {
                $resolved = self::familyConfig((string) $key);

                return [
                    'label' => $resolved['label'],
                    'fields' => $resolved['fields'],
                ];
            })->all(),
            'upholsteryFamilies' => config('catalog.upholstery_families'),
            'colours' => self::colours(),
            'finishes' => self::finishes(),
            'upholstery' => self::upholsteries(),
            'dimensionDefaults' => config('catalog.dimension_defaults'),
            'defaultBasePrice' => config('catalog.default_base_price'),
        ];
    }

    private static function configMastersReady(): bool
    {
        try {
            return Schema::hasTable('product_colours')
                && Schema::hasTable('product_finishes')
                && Schema::hasTable('product_upholsteries')
                && Schema::hasTable('catalog_config_options');
        } catch (\Throwable) {
            return false;
        }
    }
}
