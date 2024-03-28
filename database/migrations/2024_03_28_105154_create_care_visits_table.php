<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('care_visits', function (Blueprint $table) {
            $table->id();

            /** @see \App\Enums\CareVisitType */
            $table->enum('type', [
                'domestic_care',
                'personal_care',
                'meal_prep',
                'medication',
            ]);

            $table->foreignId('service_user_id')->constrained();
            $table->foreignId('care_worker_id')->constrained();

            $table->timestamp('start');
            $table->timestamp('finish');

            /** @see \App\Enums\CareVisitDeliveryStatus */
            $table->enum('delivery_status', [
                'pending',
                'delivered',
                'cancelled',
                'frustrated',
            ])->default('pending');

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('arrival_at')->nullable();
            $table->decimal('arrival_lat', 11, 8)->nullable();
            $table->decimal('arrival_lng', 11, 8)->nullable();

            $table->timestamp('departure_at')->nullable();
            $table->decimal('departure_lat', 11, 8)->nullable();
            $table->decimal('departure_lng', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_visits');
    }
};
