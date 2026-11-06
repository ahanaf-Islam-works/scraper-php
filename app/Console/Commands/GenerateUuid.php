<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('generate-uuid')]
#[Description('Generate a UUID')]
class GenerateUuid extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line(Str::uuid()->toString());

        return self::SUCCESS;
    }
}
