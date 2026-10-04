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
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('plate_number');
            $table->string('customer_name');
            $table->string('motorcycle_type');
            $table->date('service_date');
            $table->string('service_time', 10);
            $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['service_date', 'service_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
