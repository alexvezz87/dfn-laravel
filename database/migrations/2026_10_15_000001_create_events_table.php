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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('access_type', 50)->default('slots');
            $table->string('allocation_mode', 50)->nullable();
            $table->string('approval_workflow', 50)->nullable();
            $table->string('payment_mode', 50)->default('online');
            $table->decimal('price_standard', 10, 2)->default(0.00);
            $table->decimal('price_fai', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->date('event_date_start')->nullable()->index();
            $table->date('event_date_end')->nullable();
            $table->time('event_time_start')->nullable();
            $table->time('event_time_end')->nullable();
            $table->string('location', 500)->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('slot_duration')->nullable()->default(30);
            $table->unsignedInteger('slot_capacity')->nullable()->default(0);
            $table->unsignedInteger('slot_bonus')->nullable()->default(0);
            $table->time('first_slot_time')->nullable();
            $table->time('last_slot_time')->nullable();
            $table->unsignedInteger('total_capacity')->nullable()->default(0);
            $table->text('staff_config')->nullable();
            $table->string('status', 50)->nullable()->default('draft')->index();
            $table->unsignedInteger('auto_cancel_hours')->default(24);
            $table->string('detail_layout', 50)->default('auto');
            $table->dateTime('booking_opening_date')->nullable();
            $table->string('booking_status', 50)->default('open');
            $table->boolean('is_test_event')->default(false);
            $table->string('test_notification_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
