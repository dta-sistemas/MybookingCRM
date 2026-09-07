<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_holds', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('availability_slot_id')
                ->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_holds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
        });
    }
};
