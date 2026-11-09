<?php

namespace App\Enums;

enum ScrapingJobStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case TimedOut = 'timed_out';

    public function isRetryable(): bool
    {
        return match ($this) {
            self::Failed, self::TimedOut => true,
            self::Pending, self::Processing, self::Completed => false,
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::TimedOut => true,
            self::Pending, self::Processing => false,
        };
    }
}
