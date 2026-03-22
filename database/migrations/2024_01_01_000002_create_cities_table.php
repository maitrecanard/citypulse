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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('postal_code');
            $table->string('department');
            $table->string('region');
            $table->integer('population')->nullable();
            $table->foreignId('mayor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('subscription_status', ['active', 'inactive', 'trial'])->default('inactive');
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
