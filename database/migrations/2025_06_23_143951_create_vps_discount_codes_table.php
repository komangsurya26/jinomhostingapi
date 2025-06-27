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
        Schema::create('vps_discount_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vps_plan_pricing_id')->constrained('vps_plan_pricings')->onDelete('cascade');
            $table->string('code');
            $table->integer('discount_percent');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('usage_limit')->default(0);
            $table->integer('used_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vps_discount_codes');
    }
};
