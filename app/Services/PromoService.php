<?php

namespace App\Services;

use App\Models\VpsDiscountCode;
use App\Models\VpsDiscountUsage;
use App\Models\VpsPlanPricing;
use Illuminate\Support\Facades\DB;

class PromoService
{
    /**
     * Membuat code promo baru
     * 
     */
    public function createPromoCode($request)
    {
        DB::beginTransaction();
        try {
            // jika product type vps
            if ($request->productType == 'vps') {
                $vpsPlanPricing = VpsPlanPricing::find($request->planPricingId);

                if (!$vpsPlanPricing) {
                    throw new \Exception('Plan pricing not found');
                }

                $discount = VpsDiscountCode::create([
                    'vps_plan_pricing_id' => $vpsPlanPricing->id,
                    'code' => $request->code,
                    'discount_percent' => $request->discountPercent,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'usage_limit' => $request->usageLimit
                ]);
            }
            // jika product type shared hosting
            if ($request->productType == 'shared_hosting') {
                # code...
            }

            DB::commit();
            return $discount;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Mendapatkan code promo
     * 
     */
    public function applyPromoCode(int $userId, string $code, string $productType)
    {
        if ($productType == 'vps') {
            $promoCode = VpsDiscountCode::with(['vpsPlanPricing', 'vpsPlanPricing.vpsPlan', 'usages' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
                ->where('code', $code)
                ->where('end_date', '>=', now())
                ->first();

            if (!$promoCode) {
                abort(404, 'Kode promo tidak valid');
            }

            if ($promoCode->used_count === $promoCode->usage_limit) {
                abort(404, 'Kode promo sudah habis');
            }

            if ($promoCode->usages->count() > 0) {
                abort(404, 'Kode promo sudah digunakan');
            }

            return $promoCode;
        }

        if ($productType == 'shared_hosting') {
            # code...
        }
    }

    public function getPromoCode(string $productType)
    {
        if ($productType == 'vps') {
            $promoCode = VpsDiscountCode::with(['vpsPlanPricing', 'vpsPlanPricing.vpsPlan'])
                ->get();

            return $promoCode;
        }
    }

    public function updatePromoCode($request, string $promoCodeId)
    {
        DB::beginTransaction();
        try {
            // jika product type vps
            if ($request->productType == 'vps') {
                $discount = VpsDiscountCode::find($promoCodeId);
                if (!$discount) {
                    throw new \Exception('Promo not found');
                }

                $vpsPlanPricing = VpsPlanPricing::find($request->planPricingId);
                if (!$vpsPlanPricing) {
                    throw new \Exception('Plan pricing not found');
                }

                $discount->update([
                    'vps_plan_pricing_id' => $vpsPlanPricing->id,
                    'code' => $request->code,
                    'discount_percent' => $request->discountPercent,
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                    'usage_limit' => $request->usageLimit,
                ]);
            }
            // jika product type shared hosting
            if ($request->productType == 'shared_hosting') {
                # code...
            }

            DB::commit();
            return $discount;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function deletePromoCode($request, string $promoCodeId)
    {
        DB::beginTransaction();
        try {
            if ($request->productType === 'vps') {
                $discount = VpsDiscountCode::find($promoCodeId);
                if (!$discount) {
                    throw new \Exception('Promo not found');
                }
                $discount->delete();

                DB::commit();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
