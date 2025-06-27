<?php

namespace App\Http\Controllers;

use App\Services\PromoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


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
                'productType' => 'required|string|in:vps,shared_hosting',
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
}
