<?php

namespace App\Support;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LocationChoices
{
    /**
     * @return Collection<int, State>
     */
    public static function states(?int $selectedId = null): Collection
    {
        return self::activeOrSelected(State::query(), $selectedId)
            ->orderBy('state_name')
            ->get();
    }

    /**
     * @return Collection<int, District>
     */
    public static function districts(?int $stateId, ?int $selectedId = null): Collection
    {
        if (! $stateId) {
            return collect();
        }

        return self::activeOrSelected(District::query()->where('state_id', $stateId), $selectedId)
            ->orderBy('district_name')
            ->get();
    }

    /**
     * @return Collection<int, City>
     */
    public static function cities(?int $districtId, ?int $selectedId = null): Collection
    {
        if (! $districtId) {
            return collect();
        }

        return self::activeOrSelected(City::query()->where('district_id', $districtId), $selectedId)
            ->orderBy('city_name')
            ->get();
    }

    private static function activeOrSelected(Builder $query, ?int $selectedId): Builder
    {
        return $query->where(function (Builder $builder) use ($selectedId): void {
            $builder->where('status', State::STATUS_ACTIVE);

            if ($selectedId) {
                $builder->orWhere($builder->getModel()->getQualifiedKeyName(), $selectedId);
            }
        });
    }
}
