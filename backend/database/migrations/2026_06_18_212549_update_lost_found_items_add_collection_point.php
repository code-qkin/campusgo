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
    Schema::table('lost_found_items', function (Blueprint $table) {
        $table->foreignId('collection_point_id')->nullable()->after('claimed_by')
              ->constrained('collection_points')->nullOnDelete();
        $table->boolean('claim_approved')->default(false)->after('is_claimed');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
