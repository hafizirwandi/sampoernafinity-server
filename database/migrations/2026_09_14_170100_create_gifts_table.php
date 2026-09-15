<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['gift', 'sticker']);
            $table->string('tiktok_id');
            $table->string('name');
            $table->unsignedInteger('coin')->default(0);
            $table->string('image_url');
            $table->timestamps();

            $table->unique(['type', 'tiktok_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
