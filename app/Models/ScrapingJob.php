<?php

namespace App\Models;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapingJob extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'spider',
        'status',
        'callback_secret',
        'retry_count',
        'error_message',
        'records_created',
        'records_updated',
        'records_unchanged',
        'records_failed',
        'completed_at',
    ];

    protected $hidden = ['callback_secret'];

    protected function casts(): array
    {
        return [
            'spider' => ScrapingSpiderEnum::class,
            'status' => ScrapingJobStatusEnum::class,
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function postings(): HasMany
    {
        return $this->hasMany(ScrapedPosting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ScrapingJobStatusEnum::Pending->value,
            ScrapingJobStatusEnum::Processing->value,
        ]);
    }
}
