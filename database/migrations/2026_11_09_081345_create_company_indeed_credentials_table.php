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
        Schema::create('company_indeed_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
            $this->auditable($table, true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_indeed_credentials');
    }
};
