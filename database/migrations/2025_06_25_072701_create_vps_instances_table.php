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
        Schema::create('vps_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vps_plan_id')->constrained('vps_plans')->onDelete('cascade');
            $table->foreignId('order_detail_id')->constrained('order_details')->onDelete('cascade');
            $table->foreignId('vps_os_id')->constrained('vps_os')->onDelete('cascade');
            $table->enum('status', ['active', 'suspended', 'terminated', 'pending'])->default('pending');
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('panel_username')->nullable();
            $table->string('panel_password_hash')->nullable();
            $table->bigInteger('duration_months')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vps_instances');
    }
};
