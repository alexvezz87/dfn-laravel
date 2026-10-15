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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone', 50)->nullable();
            $table->unsignedInteger('total_persons')->default(1);
            $table->unsignedInteger('persons_standard')->default(0);
            $table->unsignedInteger('persons_fai')->default(0);
            $table->string('status', 50)->default('confirmed')->index();
            $table->string('qr_code_hash', 128)->nullable()->index();
            $table->string('checkin_status', 50)->default('pending');
            $table->dateTime('checkin_at')->nullable();
            $table->unsignedBigInteger('checked_in_by')->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->decimal('amount_due', 10, 2)->default(0.00);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
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
