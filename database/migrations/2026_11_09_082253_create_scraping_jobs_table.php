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
        Schema::create('scraping_jobs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('spider', 64);
            $table->string('status', 32)->default('pending');
            $table->string('callback_secret', 64);
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('records_created')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_unchanged')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $this->auditable($table, true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
