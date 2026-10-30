<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();
            $t->date('event_date');
            $t->string('time_text')->nullable();
            $t->string('venue')->nullable();
            $t->string('city')->nullable();
            $t->text('intro')->nullable();
            $t->longText('highlights')->nullable();
            $t->string('cover_image')->nullable();
            $t->string('qr_image')->nullable();
            $t->string('register_url')->nullable();
            $t->string('contact_phone', 30)->nullable();
            $t->string('contact_email')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('event_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->string('image');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_images');
        Schema::dropIfExists('events');
    }
};
