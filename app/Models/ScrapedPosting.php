<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class ScrapedPosting extends Model
{
    protected $fillable = [
        'scraping_job_id',
        'provider',
        'external_id',
        'row_hash',
        'payload',
        'synced_at',
    ];


    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'synced_at' => 'datetime'
        ];
    }

    public function scrapingJob(): BelongsTo
    {
        return $this->belongsTo(ScrapingJob::class);
    }
}
