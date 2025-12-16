<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Settings are stored as key-value pairs, so we don't need to alter the table structure
        // We'll just seed default values if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to rollback as settings are key-value pairs
    }
};
