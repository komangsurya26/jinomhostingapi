<?php

namespace App\Services;

use App\Models\VpsPlan;
use App\Models\VpsPlanFeature;
use App\Models\VpsPlanPricing;
use Illuminate\Support\Facades\DB;

class VpsService
{
    public function __construct(
        protected VirtualizorService $virtualizor,
    ) {}
    /**
     * Membuat vps baru
     *
     * 
     */
    public function createVps(
        int $vpsPlanId,
        string $hostname,
        string $rootPassword,
        int $osId,
        string $email,
        string $password
    ) {
        $vpsPlan = VpsPlan::find($vpsPlanId);

        $available_ips = $this->virtualizor->listIPs();

        $createVps = $this->virtualizor->addVPS([
            "hostname" => $hostname,
            "rootpass" => $rootPassword,
            "user_email" => $email,
            "user_pass" => $password,
            "osid" => $osId,
            "ram" => $vpsPlan->ram,
            "swapram" => 0,
            "bandwidth" => 0,
            "space" => $vpsPlan->storage,
            "cpu" => 1000,
            "cores" => $vpsPlan->cpu,
            "virt" => "kvm",
            "plid" => 0,
            "uid" => 0,
            "ips" => array($available_ips[0]),
            "stid" => 2
        ]);

        return $createVps;
    }


    public function createVpsPlan(
        string $name,
        string $cpu,
        string $ram,
        string $storage,
        string $bandwidth,
        string $tagline,
        string $description,
        bool $isFeatured,
        int $displayOrder,
        array $vpsFeatures,
        array $vpsPricings
    ) {
        try {
            $vpsPlan = VpsPlan::create([
                'name' => $name,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'bandwidth' => $bandwidth,
                'tagline' => $tagline,
                'description' => $description,
                'is_featured' => $isFeatured ?? false,
                'display_order' => $displayOrder,
            ]);

            foreach ($vpsFeatures as $feature) {
                VpsPlanFeature::create([
                    'vps_plan_id' => $vpsPlan->id,
                    'content' => $feature['content'],
                    'highlight' => $feature['highlight'],
                    'display_order' => $feature['displayOrder']
                ]);
            }

            foreach ($vpsPricings as $item) {
                $durationMonths = $item['durationMonths'];
                $basePrice = $item['basePrice'];
                $discountPercent = $item['discountPercent'] ?? 0;

                $finalPrice = $basePrice * (1 - $discountPercent / 100);
                $basePricePerMonth = $basePrice / $durationMonths;
                $finalPricePerMonth = $finalPrice / $durationMonths;

                VpsPlanPricing::create([
                    'vps_plan_id' => $vpsPlan->id,
                    'duration_months' => $durationMonths,
                    'base_price' => $basePrice,
                    'base_price_per_month' => $basePricePerMonth,
                    'final_price' => $finalPrice,
                    'final_price_per_month' => $finalPricePerMonth,
                    'discount_percent' => $discountPercent,
                ]);
            }

            return $vpsPlan;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateVpsPlan(
        int $productId,
        string $name,
        string $cpu,
        string $ram,
        string $storage,
        string $bandwidth,
        string $tagline,
        string $description,
        bool $isFeatured,
        int $displayOrder,
        array $vpsFeatures,
        array $vpsPricings
    ) {
        DB::beginTransaction();
        try {
            $vpsPlan = VpsPlan::find($productId);

            if (!$vpsPlan) {
                abort(404, 'VPS plan not found');
            }

            $vpsPlan->update([
                'name' => $name,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'bandwidth' => $bandwidth,
                'tagline' => $tagline,
                'description' => $description,
                'is_featured' => $isFeatured,
                'display_order' => $displayOrder,
            ]);

            $vpsPlan->features()->delete();

            foreach ($vpsFeatures as $feature) {
                $vpsPlan->features()->create([
                    'content' => $feature['content'],
                    'highlight' => $feature['highlight'],
                    'display_order' => $feature['displayOrder'],
                ]);
            }

            $vpsPlan->pricing()->delete();

            foreach ($vpsPricings as $pricing) {
                $duration = $pricing['durationMonths'];
                $base = $pricing['basePrice'];
                $disc = $pricing['discountPercent'];
                $final = $base * (1 - $disc / 100);

                $vpsPlan->pricing()->create([
                    'duration_months' => $duration,
                    'base_price' => $base,
                    'base_price_per_month' => $base / $duration,
                    'final_price' => $final,
                    'final_price_per_month' => $final / $duration,
                    'discount_percent' => $disc,
                ]);
            }

            DB::commit();
            return $vpsPlan;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getVpsPlan(?string $productId)
    {
        return VpsPlan::with('features', 'pricing')
            ->when($productId, function ($query) use ($productId) {
                return $query->where('id', $productId);
            })
            ->get();
    }
}
