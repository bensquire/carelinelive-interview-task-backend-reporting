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
        Schema::create('service_users', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('location_lat', 11, 8);
            $table->decimal('location_lng', 11, 8);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_users');
    }
};