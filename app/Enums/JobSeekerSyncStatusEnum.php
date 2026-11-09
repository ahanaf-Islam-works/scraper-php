<?php

namespace App\Enums;


enum JobSeekerSyncStatusEnum: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';

    public function isRetryable(): bool
    {
        return match ($this) {
            self::Failed => true,
            self::Pending, self::Synced => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting to sync',
            self::Synced => 'Synced',
            self::Failed => 'Sync failed',
        };
    }

    public static function fromIngestOutcome(IngestOutcomeEnum $outcome): self
    {
        return match ($outcome) {
            IngestOutcomeEnum::Created, IngestOutcomeEnum::Updated => self::Pending,
            IngestOutcomeEnum::Unchanged => self::Synced,
        };
    }
}
