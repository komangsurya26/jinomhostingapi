<?php

namespace App\Http\Controllers\Admin;

use App\Services\PromoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class PromoCodeController extends Controller
{
    public function __construct(
        protected PromoService $promo,
    ) {}

    /**
     * Store Promo Code
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ([
                'productType' => 'required|string|in:vps',
                'planPricingId' => 'required|numeric',
                'code' => 'required|string',
                'discountPercent' => 'required|numeric',
                'startDate' => 'required|date',
                'endDate' => 'required|date',
                'usageLimit' => 'required|numeric'
            ]));

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $promoCode = $this->promo->createPromoCode($request);

            return response()->json([
                'error' => false,
                'message' => 'Create code promo success',
                'data' => $promoCode
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function get(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ([
                'productType' => 'required|string|in:vps,shared_hosting'
            ]));

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $promoCode = $this->promo->getPromoCode($request->productType);

            return response()->json([
                'error' => false,
                'message' => 'Get code promo success',
                'data' => $promoCode
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'productType' => 'required|string|in:vps,shared_hosting',
                'planPricingId' => 'required|numeric',
                'code' => 'required|string',
                'discountPercent' => 'required|numeric',
                'startDate' => 'required|date',
                'endDate' => 'required|date',
                'usageLimit' => 'required|numeric'
            ], [
                'productType.in' => 'Tipe produk tidak valid, gunakan "vps" atau "shared_hosting"'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $promoCode = $this->promo->updatePromoCode($request, $id);

            return response()->json([
                'error' => false,
                'message' => 'Update code promo success',
                'data' => $promoCode
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'productType' => 'required|string|in:vps,shared_hosting'
            ], [
                'productType.required' => 'Tipe produk wajib diisi',
                'productType.in' => 'Tipe produk tidak valid, gunakan "vps" atau "shared_hosting"'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $this->promo->deletePromoCode($request, $id);

            return response()->json([
                'error' => false,
                'message' => 'Delete code promo success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
