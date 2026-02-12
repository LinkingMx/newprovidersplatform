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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('status', ['created', 'invited', 'registered', 'profile_completed', 'active'])->default('created');
            $table->string('password_hash')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();

            // Profile address data
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_zip')->nullable();

            // Banking data
            $table->string('clabe_interbancaria')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
