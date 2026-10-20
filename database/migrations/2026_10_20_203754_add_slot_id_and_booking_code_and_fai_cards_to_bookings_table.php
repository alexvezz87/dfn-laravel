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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('slot_id')
                ->nullable()
                ->after('event_id')
                ->constrained('slots')
                ->nullOnDelete();

            $table->string('booking_code', 32)
                ->nullable()
                ->unique()
                ->after('order_id');

            $table->json('fai_cards')
                ->nullable()
                ->after('persons_fai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['slot_id']);
            $table->dropColumn(['slot_id', 'booking_code', 'fai_cards']);
        });
    }
};
