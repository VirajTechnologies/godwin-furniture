<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use UpdatesActiveStatus;

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'type' => $request->string('type')->toString(),
            'status' => $request->string('status')->toString(),
            'room_id' => $request->integer('room_id') ?: null,
        ];

        if (! in_array($filters['type'], ['', 'room', 'subcategory'], true)) {
            $filters['type'] = '';
        }

        if (! in_array($filters['status'], ['', Category::STATUS_ACTIVE, Category::STATUS_INACTIVE], true)) {
            $filters['status'] = '';
        }

        $roomsQuery = Category::query()
            ->whereNull('parent_id')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($filters['room_id']) {
            $roomsQuery->whereKey($filters['room_id']);
        }

        $this->applyRoomFilters($roomsQuery, $filters);

        $roomsQuery->with(['children' => function ($query) use ($filters): void {
            $query->withCount('products')
                ->orderBy('menu_group')
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($filters['type'] === 'room') {
                $query->whereRaw('0 = 1');

                return;
            }

            $this->applyChildFilters($query, $filters, roomNameMatched: false);
        }]);

        $rooms = $roomsQuery->paginate(10)->withQueryString();

        // When the room name matched the search, show all children (status still applied).
        if ($filters['q'] !== '' && $filters['type'] !== 'room' && $filters['type'] !== 'subcategory') {
            $rooms->getCollection()->transform(function (Category $room) use ($filters): Category {
                $roomMatched = str_contains(mb_strtolower($room->name), mb_strtolower($filters['q']));

                if ($roomMatched) {
                    $room->setRelation(
                        'children',
                        $room->children()
                            ->withCount('products')
                            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
                            ->orderBy('menu_group')
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get()
                    );
                }

                return $room;
            });
        }

        return view('admin.categories.index', [
            'rooms' => $rooms,
            'filterRooms' => $this->rooms(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->integer('parent_id') ?: null;

        if ($parentId && ! Category::query()->whereNull('parent_id')->whereKey($parentId)->exists()) {
            $parentId = null;
        }

        return view('admin.categories.create', [
            'category' => new Category([
                'status' => Category::STATUS_ACTIVE,
                'sort_order' => 0,
                'parent_id' => $parentId,
            ]),
            'rooms' => $this->rooms(),
            'menuGroupsByRoom' => $this->menuGroupsByRoom(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category saved.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'rooms' => $this->rooms($category),
            'menuGroupsByRoom' => $this->menuGroupsByRoom(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function updateStatus(Request $request, Category $category): RedirectResponse
    {
        return $this->updateActiveStatus($request, $category, $category->name);
    }

    /**
     * @param  array{q: string, type: string, status: string, room_id: int|null}  $filters
     */
    private function applyRoomFilters(Builder $query, array $filters): void
    {
        if ($filters['type'] === 'room') {
            if ($filters['q'] !== '') {
                $query->where('name', 'like', '%'.$filters['q'].'%');
            }

            if ($filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }

            return;
        }

        if ($filters['type'] === 'subcategory') {
            $query->whereHas('children', fn (Builder $children) => $this->applyChildFilters($children, $filters));

            return;
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['q'] !== '') {
            $query->where(function (Builder $builder) use ($filters): void {
                $builder->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhereHas('children', fn (Builder $children) => $this->applyChildFilters($children, $filters));
            });
        }
    }

    /**
     * @param  array{q: string, type: string, status: string, room_id: int|null}  $filters
     */
    private function applyChildFilters(Builder|Relation $query, array $filters, bool $roomNameMatched = false): void
    {
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (! $roomNameMatched && $filters['q'] !== '' && $filters['type'] !== 'room') {
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }
    }

    private function rooms(?Category $except = null)
    {
        return Category::query()
            ->whereNull('parent_id')
            ->when($except, fn ($query) => $query->where('id', '!=', $except->id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, list<string>>
     */
    private function menuGroupsByRoom(): array
    {
        $groups = [];

        Category::query()
            ->whereNotNull('parent_id')
            ->whereNotNull('menu_group')
            ->where('menu_group', '!=', '')
            ->orderBy('menu_group')
            ->get(['parent_id', 'menu_group'])
            ->each(function (Category $category) use (&$groups): void {
                $roomId = (string) $category->parent_id;
                $groups[$roomId] ??= [];

                if (! in_array($category->menu_group, $groups[$roomId], true)) {
                    $groups[$roomId][] = $category->menu_group;
                }
            });

        foreach ($groups as $roomId => $names) {
            sort($names, SORT_NATURAL | SORT_FLAG_CASE);
            $groups[$roomId] = array_values($names);
        }

        return $groups;
    }
}
