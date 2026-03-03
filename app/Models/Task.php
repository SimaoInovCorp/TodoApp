<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    public const PRIORITIES = [
        self::PRIORITY_HIGH,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_LOW,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'status' => 'boolean',
        ];
    }

    /**
     * Scope tasks to the authenticated user.
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->whereBelongsTo($user);
    }

    /**
     * Scope the query using filter parameters.
     */
    public function scopeApplyFilters(Builder $query, array $filters): void
    {
        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', ($filters['status'] ?? 'pending') === 'completed');
        }

        if (! empty($filters['priority']) && in_array($filters['priority'], self::PRIORITIES, true)) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        if (! empty($filters['search'])) {
            $query->where(static function (Builder $builder) use ($filters): void {
                $builder
                    ->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }
    }

    /**
     * Get the owning user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
