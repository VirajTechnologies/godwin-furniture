<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersCatalogMasters
{
    /**
     * @param  list<string>  $extraKeys
     * @return array<string, string>
     */
    protected function catalogFilters(Request $request, array $extraKeys = []): array
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
        ];

        foreach ($extraKeys as $key) {
            $filters[$key] = $request->string($key)->trim()->toString();
        }

        if (! in_array($filters['status'], ['', 'active', 'inactive'], true)) {
            $filters['status'] = '';
        }

        if (array_key_exists('show_on_store', $filters) && ! in_array($filters['show_on_store'], ['', '1', '0'], true)) {
            $filters['show_on_store'] = '';
        }

        return $filters;
    }

    /**
     * @param  array{q: string, status: string, show_on_store?: string}  $filters
     */
    protected function applyCatalogFilters(Builder $query, array $filters, string $nameColumn = 'name'): Builder
    {
        return $query
            ->when($filters['q'] !== '', fn (Builder $builder) => $builder->where($nameColumn, 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] !== '', fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(
                array_key_exists('show_on_store', $filters) && $filters['show_on_store'] !== '',
                fn (Builder $builder) => $builder->where('show_on_store', $filters['show_on_store'] === '1')
            );
    }
}
