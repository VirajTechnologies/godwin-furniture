<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRequest extends Model
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const MODE_MATCHED = 'matched';

    public const MODE_UNMATCHED = 'unmatched';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'agent_id',
        'branch_id',
        'match_mode',
        'product_id',
        'project_category',
        'room',
        'product_category',
        'design_type',
        'model_name',
        'description',
        'width_cm',
        'depth_cm',
        'height_cm',
        'weight_kg',
        'material',
        'base_price',
        'specs',
        'variants',
        'customer_name',
        'customer_phone',
        'notes',
        'status',
        'rejection_reason',
        'requested_by',
        'handled_by',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width_cm' => 'float',
            'depth_cm' => 'float',
            'height_cm' => 'float',
            'weight_kg' => 'float',
            'base_price' => 'float',
            'specs' => 'array',
            'variants' => 'array',
            'cancelled_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isMatched(): bool
    {
        return $this->match_mode === self::MODE_MATCHED;
    }

    public function displayName(): string
    {
        return $this->model_name
            ?: $this->product?->name
            ?: 'Custom request';
    }

    public function classificationTrail(): string
    {
        return collect([
            $this->project_category,
            $this->room,
            $this->product_category,
            $this->design_type,
            $this->model_name,
        ])->filter()->implode(' › ');
    }

    public function isRequested(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_REQUESTED,
            self::STATUS_ACCEPTED,
            self::STATUS_IN_PROGRESS,
        ], true);
    }

    public function label(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => [self::STATUS_ACCEPTED, self::STATUS_REJECTED],
            self::STATUS_ACCEPTED => [self::STATUS_IN_PROGRESS, self::STATUS_REJECTED],
            self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED],
            default => [],
        };
    }

    public function transitionTo(string $status, ?User $handler = null, ?string $rejectionReason = null): void
    {
        if (! in_array($status, $this->allowedNextStatuses(), true)) {
            throw new \InvalidArgumentException('This custom request cannot move to '.$status.'.');
        }

        $payload = [
            'status' => $status,
            'handled_by' => $handler?->id ?? $this->handled_by,
        ];

        if ($status === self::STATUS_REJECTED) {
            $payload['rejection_reason'] = $rejectionReason;
        }

        $this->update($payload);
    }
}
