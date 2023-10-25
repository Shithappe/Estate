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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('description');
            $table->string('builder_name');
            $table->string('complex_name')->nullable();

            $table->string('square');
            $table->string('price_per_meter');
            $table->string('room_count');
            $table->integer('floor');
            // photos
            $table->string('main_image');
            $table->json('images')->nullable();
            
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('street');
            $table->string('coordinate')->nullable();

            $table->integer('rate');
            $table->string('property_type');
            $table->string('bedrooms_count')->nullable();
            $table->string('source_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
