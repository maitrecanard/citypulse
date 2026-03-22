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
        Schema::create('doleances', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['voirie', 'eclairage', 'proprete', 'bruit', 'securite', 'autre']);
            $table->enum('priority', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->enum('status', ['nouvelle', 'en_cours', 'resolue', 'rejetee'])->default('nouvelle');
            $table->text('admin_response')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->timestamp('consulted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doleances');
    }
};
