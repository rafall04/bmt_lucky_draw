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
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            $table->string('no_rekening')->unique()->index();
            $table->string('nama');
            $table->text('alamat');
            $table->string('cabang')->nullable();
            $table->boolean('status_menang')->default(0)->index();
            $table->string('hadiah_didapat')->nullable();
            $table->timestamp('waktu_menang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};

