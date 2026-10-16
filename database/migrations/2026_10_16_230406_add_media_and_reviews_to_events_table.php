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
        Schema::table('events', function (Blueprint $table) {
            $table->string('featured_image', 500)->nullable()->after('detail_layout');
            $table->json('gallery_images')->nullable()->after('featured_image');
            $table->json('wall_media')->nullable()->after('gallery_images');
            $table->boolean('show_reviews_frontend')->default(true)->after('wall_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'featured_image',
                'gallery_images',
                'wall_media',
                'show_reviews_frontend',
            ]);
        });
    }
};
