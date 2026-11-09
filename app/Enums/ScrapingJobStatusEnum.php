<?php

namespace App\Enums;

enum ScrapingJobStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case TimedOut = 'timeout';

    public function isRetryable(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Failed,
            self::TimedOut,
        ], true);
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::TimedOut => true,
            self::Pending, self::Processing => false,
            default => false,
        };
    }
}
