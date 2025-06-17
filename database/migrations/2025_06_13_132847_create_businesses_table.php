<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('business_slug')->unique()->nullable();
            $table->text('description');
            $table->string('tagline')->nullable();
            $table->string('industry');
            $table->string('business_type');
            $table->date('founded_date')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('primary_email');
            $table->string('phone_number')->nullable();
            $table->string('website_url')->nullable();
            $table->string('street_address');
            $table->string('city');
            $table->string('state_province');
            $table->string('postal_code');
            $table->string('country');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('logo_path')->nullable();
            $table->json('business_hours')->nullable();
            $table->json('services_offered')->nullable();
            $table->integer('employee_count')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_verified']);
            $table->index(['industry']);
            $table->index(['city', 'state_province']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
