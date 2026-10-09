<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {

        Schema::enableForeignKeyConstraints();

        /* 3. CHURCH:  rites -> dioceses -> foranes -> parishes */
        Schema::create('rites', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('dioceses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rite_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['rite_id', 'name']);
            $t->timestamps();
        });
        Schema::create('foranes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('diocese_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['diocese_id', 'name']);
            $t->timestamps();
        });
        Schema::create('parishes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('forane_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['forane_id', 'name']);
            $t->timestamps();
        });

        /* 4. LOCATION:  countries -> states -> districts */
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('states', function (Blueprint $t) {
            $t->id();
            $t->foreignId('country_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['country_id', 'name']);
            $t->timestamps();
        });
        Schema::create('districts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('state_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['state_id', 'name']);
            $t->timestamps();
        });

        /* 5. JOBS:  job_categories -> jobs */
        Schema::create('job_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_category_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unique(['job_category_id', 'name']);
            $t->timestamps();
        });

        /* 6. users -> parish (rite, diocese and forane are reached through the parish) */
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('parish_id')->nullable()->constrained()->nullOnDelete();
        });

        /* 7. PROFILE (1 : 1 with users). name, phone, email stay on users. */
        Schema::create('profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $t->string('photo')->nullable();
            $t->string('cover')->nullable();
            $t->string('about', 300)->nullable();

            // personal
            $t->date('dob')->nullable();
            $t->string('gender', 10)->nullable();
            $t->string('blood_group', 5)->nullable();

            // contact (mobile = users.phone, email = users.email)
            $t->string('whatsapp', 20)->nullable();
            $t->string('preferred_contact', 10)->nullable();      // phone | whatsapp

            // employment
            $t->string('employment_status', 30)->nullable();
            $t->string('organization')->nullable();
            $t->string('work_mode', 20)->nullable();
            $t->unsignedTinyInteger('experience_years')->nullable();

            // job (category is reached through the job)
            $t->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
$t->text('work_description')->nullable();
            $t->timestamps();
        });

        /* 8. ADDRESSES: one row per type per user */
        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type', 10);                               // permanent | present
            $t->string('house')->nullable();
            $t->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $t->string('pin', 10)->nullable();
            $t->unique(['user_id', 'type']);
            $t->timestamps();
        });

        /* 9. SOCIAL LINKS + GALLERY (many per user) */
        Schema::create('social_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name', 30);                               // instagram, facebook, youtube, x, linkedin
            $t->string('url');
            $t->unique(['user_id', 'name']);
            $t->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('title')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('parish_id'));
        foreach (['gallery_images', 'social_links', 'addresses', 'profiles', 'jobs', 'job_categories',
                  'parishes', 'foranes', 'dioceses', 'rites', 'districts', 'states', 'countries'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};
