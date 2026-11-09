<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

trait HasAuditableColumns
{
    protected function auditable(Blueprint $table, bool $softDeletes = false): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

        if ($softDeletes) {
            $table->softDeletes();
        }
    }
}
