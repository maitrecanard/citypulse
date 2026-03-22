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
        Schema::table('users', function (Blueprint $table) {
            $table->char('uuid', 36)->unique()->after('id');
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->text('address')->nullable()->after('phone');
            $table->enum('role', ['administre', 'maire', 'secretaire', 'agent'])->default('administre')->after('address');
            $table->foreignId('city_id')->nullable()->after('role')->constrained('cities')->nullOnDelete();

            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropIndex(['uuid']);
            $table->dropColumn([
                'uuid',
                'first_name',
                'last_name',
                'phone',
                'address',
                'role',
                'city_id',
            ]);
        });
    }
};
