<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['status' => Category::STATUS_ACTIVE]),
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
}
