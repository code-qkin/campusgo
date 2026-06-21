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
        Schema::create('carpool_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('carpool_rides')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('boarding_stop_id')->constrained('route_stops');
            $table->foreignId('exit_stop_id')->constrained('route_stops');
            $table->decimal('fare', 8, 2);
            $table->string('join_type');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpool_passengers');
    }
};
