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
        Schema::create('vps_plan_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vps_plan_id')->constrained('vps_plans')->onDelete('cascade');
            $table->integer('duration_months');
            $table->bigInteger('base_price');
            $table->bigInteger('base_price_per_month');
            $table->bigInteger('final_price_per_month');
            $table->integer('discount_percent')->default(0);
            $table->bigInteger('final_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vps_plan_pricings');
    }
};
