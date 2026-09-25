<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
   public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
 
            $table->string('name')->nullable();          
            $table->string('email')->nullable()->unique(); 
            $table->string('password')->nullable();       
 
            $table->string('phone')->nullable()->unique(); 
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('parish')->nullable();
            $table->string('diocese')->nullable();
            $table->string('rite')->nullable();
 
          
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
 
            $table->rememberToken();
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
