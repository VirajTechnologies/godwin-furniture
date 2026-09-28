<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasActiveStatus;

    public const SUPER_ADMIN = 'super_admin';

    public const WAREHOUSE_STAFF = 'warehouse_staff';

    public const BRANCH_MANAGER = 'branch_manager';

    public const BRANCH_STAFF = 'branch_staff';

    /**
     * @return list<string>
     */
    public static function branchSlugs(): array
    {
        return [self::BRANCH_MANAGER, self::BRANCH_STAFF];
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
