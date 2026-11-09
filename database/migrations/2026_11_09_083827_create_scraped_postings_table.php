<?php

use App\Traits\HasAuditableColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasAuditableColumns;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scraped_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraping_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('external_id');
            $table->char('row_hash', 64);
            $table->json('payload');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $this->auditable($table, true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_postings');
    }
};
