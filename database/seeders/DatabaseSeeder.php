<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VpsOs;
use App\Models\VpsPlan;
use App\Models\VpsPlanPricing;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'admin',
        //     'email' => 'admin@jinom.net',
        //     'phone' => '08123456789',
        //     'password' => "jinom1718",
        //     'role' => 'admin',
        //     'email_verified_at' => now()
        // ]);

        // VpsPlan::create([
        //     'name' => 'KVM Starter',
        //     'cpu' => '1',
        //     'ram' => '1024',
        //     'storage' => '10',
        //     'bandwidth' => '2',
        //     'tagline' => 'Paling Hemat!',
        //     'description' => 'Untuk Penggunaan Pribadi',
        //     'is_featured' => false,
        //     'display_order' => 1,
        // ]);
        // VpsPlan::create([
        //     'name' => 'KVM Basic',
        //     'cpu' => '2',
        //     'ram' => '2048',
        //     'storage' => '30',
        //     'bandwidth' => '4',
        //     'tagline' => 'Paling Populer!',
        //     'description' => 'Untuk Penggunaan Bisnis Kecil',
        //     'is_featured' => true,
        //     'display_order' => 2,
        // ]);
        // VpsPlan::create([
        //     'name' => 'KVM Pro',
        //     'cpu' => '4',
        //     'ram' => '4096',
        //     'storage' => '50',
        //     'bandwidth' => '8',
        //     'tagline' => 'Terbaik!',
        //     'description' => 'Untuk Penggunaan Bisnis Menengah',
        //     'is_featured' => false,
        //     'display_order' => 3,
        // ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 1,
            'duration_months' => 1,
            'base_price' => 109000,
            'base_price_per_month' => 109000, // base_price per month for 1 month
            'final_price_per_month' => 109000, // final price per month (no discount)
            'discount_percent' => 0,
            'final_price' => 109000,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 1,
            'duration_months' => 3,
            'base_price' => 327000,
            'base_price_per_month' => 109000, // base price divided by 3 months
            'final_price_per_month' => 98000, // final price after 10% discount (327000 * 0.9 / 3)
            'discount_percent' => 10,
            'final_price' => 294300,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 1,
            'duration_months' => 6,
            'base_price' => 654000,
            'base_price_per_month' => 109000, // base price divided by 6 months
            'final_price_per_month' => 87300, // final price after 20% discount (654000 * 0.8 / 6)
            'discount_percent' => 20,
            'final_price' => 523200,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 1,
            'duration_months' => 12,
            'base_price' => 1308000,
            'base_price_per_month' => 109000, // base price divided by 12 months
            'final_price_per_month' => 76300, // final price after 30% discount (1308000 * 0.7 / 12)
            'discount_percent' => 30,
            'final_price' => 915600,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 2,
            'duration_months' => 1,
            'base_price' => 218000,
            'base_price_per_month' => 218000, // base price for 1 month
            'final_price_per_month' => 218000, // final price per month (no discount)
            'discount_percent' => 0,
            'final_price' => 218000,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 2,
            'duration_months' => 3,
            'base_price' => 654000,
            'base_price_per_month' => 218000, // base price divided by 3 months
            'final_price_per_month' => 196200, // final price after 10% discount (654000 * 0.9 / 3)
            'discount_percent' => 10,
            'final_price' => 588600,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 2,
            'duration_months' => 6,
            'base_price' => 1308000,
            'base_price_per_month' => 218000, // base price divided by 6 months
            'final_price_per_month' => 174000, // final price after 20% discount (1308000 * 0.8 / 6)
            'discount_percent' => 20,
            'final_price' => 1046400,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 2,
            'duration_months' => 12,
            'base_price' => 2616000,
            'base_price_per_month' => 218000, // base price divided by 12 months
            'final_price_per_month' => 152760, // final price after 30% discount (2616000 * 0.7 / 12)
            'discount_percent' => 30,
            'final_price' => 1831200,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 3,
            'duration_months' => 1,
            'base_price' => 437000,
            'base_price_per_month' => 437000, // base price for 1 month
            'final_price_per_month' => 437000, // final price per month (no discount)
            'discount_percent' => 0,
            'final_price' => 437000,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 3,
            'duration_months' => 3,
            'base_price' => 1311000,
            'base_price_per_month' => 437000, // base price divided by 3 months
            'final_price_per_month' => 393333, // final price after 10% discount (1311000 * 0.9 / 3)
            'discount_percent' => 10,
            'final_price' => 1180000,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 3,
            'duration_months' => 6,
            'base_price' => 2622000,
            'base_price_per_month' => 437000, // base price divided by 6 months
            'final_price_per_month' => 349600, // final price after 20% discount (2622000 * 0.8 / 6)
            'discount_percent' => 20,
            'final_price' => 2097600,
        ]);

        VpsPlanPricing::create([
            'vps_plan_id' => 3,
            'duration_months' => 12,
            'base_price' => 5244000,
            'base_price_per_month' => 437000, // base price divided by 12 months
            'final_price_per_month' => 306090, // final price after 30% discount (5244000 * 0.7 / 12)
            'discount_percent' => 30,
            'final_price' => 3670800,
        ]);

        VpsOs::create([
            'name' => 'Windows',
            'os_id' => 1145,
            'image_url' => 'https://assets.pikiran-rakyat.com/crop/0x0:0x0/x/photo/2021/08/16/310400.jpg',
        ]);
        VpsOs::create([
            'name' => 'Ubuntu',
            'os_id' => 1017,
            'image_url' => 'https://assets.pikiran-rakyat.com/crop/0x0:0x0/x/photo/2021/08/16/310400.jpg',
        ]);
        VpsOs::create([
            'name' => 'Centos',
            'os_id' => 961,
            'image_url' => 'https://assets.pikiran-rakyat.com/crop/0x0:0x0/x/photo/2021/08/16/310400.jpg',
        ]);
    }
}
