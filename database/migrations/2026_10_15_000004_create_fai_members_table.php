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
        Schema::create('fai_members', function (Blueprint $table) {
            $table->id();
            $table->string('card_number', 50)->index();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 50)->nullable();
            $table->string('status', 50)->default('active')->index();
            $table->date('expiry_date')->nullable()->index();
            $table->boolean('verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('card_type', 50)->default('INDIVIDUALE');
            $table->boolean('is_volunteer')->default(false)->index();
            $table->string('volunteer_status', 50)->default('active');
            $table->text('volunteer_notes')->nullable();
            $table->date('joined_date')->nullable();
            $table->boolean('is_guide')->default(false);
            $table->boolean('has_safety_course')->default(false);
            $table->string('fai_registry_id', 50)->nullable();
            $table->boolean('is_sivol_registered')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fai_members');
    }
};
