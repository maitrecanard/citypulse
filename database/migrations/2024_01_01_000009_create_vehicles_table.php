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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name');
            $table->enum('type', ['voiture', 'camion', 'utilitaire', 'engin', 'autre']);
            $table->string('plate_number');
            $table->string('team')->nullable();
            $table->enum('status', ['disponible', 'en_service', 'maintenance', 'hors_service'])->default('disponible');
            $table->date('next_maintenance_at')->nullable();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
