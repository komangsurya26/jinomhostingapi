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
        Schema::create('vps_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpu');
            $table->string('ram');
            $table->string('storage');
            $table->string('bandwidth');
            $table->string('tagline');
            $table->longText('description');
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vps_plans');
    }
};
