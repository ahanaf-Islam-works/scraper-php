<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GenerateUuidTest extends TestCase
{
    public function test_it_generates_a_uuid(): void
    {
        $exitCode = Artisan::call('generate-uuid');
        $output = trim(Artisan::output());

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $output,
        );
    }
}
