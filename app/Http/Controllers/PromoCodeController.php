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
     * Apply Promo Code
     */
    public function get(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'productType' => 'required|string|in:vps,shared_hosting'
            ], [
                'code.required' => 'Kode voucher wajib diisi',
                'productType.required' => 'Tipe produk wajib diisi',
                'productType.in' => 'Tipe produk tidak valid, gunakan "vps" atau "shared_hosting"'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $promoCode = $this->promo->getPromoCode($user->id, $request->code, $request->productType);

            return response()->json([
                'error' => false,
                'message' => 'Get code promo success',
                'data' =>  $promoCode
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
