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
        Schema::create('vps_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vps_plan_id')->constrained('vps_plans')->onDelete('cascade');
            $table->text('content');
            $table->boolean('highlight')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vps_plan_features');
    }
};
