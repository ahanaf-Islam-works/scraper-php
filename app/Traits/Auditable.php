<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 *
 * @phpstan-require-extends Model
 *
 * @method static void creating(mixed $callback)
 * @method static void updating(mixed $callback)
 * @method static void deleting(mixed $callback)
 * @method static void restoring(mixed $callback)
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        // Automatically set creator and updater
        static::creating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId) {
                if ($model->getAttribute('created_by') === null) {
                    $model->setAttribute('created_by', $userId);
                }

                if ($model->getAttribute('updated_by') === null) {
                    $model->setAttribute('updated_by', $userId);
                }
            }
        });

        // Automatically update updater
        static::updating(function (Model $model): void {
            if ($userId = Auth::id()) {
                $model->setAttribute('updated_by', $userId);
            }
        });

        // Automatically set deleter (for soft deletes)
        static::deleting(function (Model $model): void {
            if (! in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
                return;
            }

            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            if ($userId = Auth::id()) {
                $model->setAttribute('deleted_by', $userId);

                // A soft delete only persists deleted_at/updated_at, so save the
                // deleter explicitly. Quietly, to avoid re-triggering model events.
                $model->saveQuietly();
            }
        });

        // Clear deleter when restoring. The restoring event only exists on
        // models that use SoftDeletes, so don't register it for other models.
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restoring(function (Model $model): void {
                $model->setAttribute('deleted_by', null);
            });
        }
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
