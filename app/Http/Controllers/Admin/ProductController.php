<?php

namespace App\Http\Controllers\Admin;

use App\Models\VpsInstance;
use App\Services\PromoService;
use App\Services\VpsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    public function __construct(
        protected PromoService $promo,
        protected VpsService $vps
    ) {}

    /**
     * Update the specified resource in storage.
     */
    public function get(Request $request, string $productType)
    {
        try {
            if ($productType == 'vps') {
                $validator = Validator::make($request->all(), [
                    'productId' => 'nullable|string|exists:vps_plans,id'
                ], [
                    'productId.exists' => 'Product ID tidak ditemukan dalam daftar VPS plans'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validator->errors()->all()[0]
                    ], 422);
                }

                $products = $this->vps->getVpsPlan($request->productId);
            }


            return response()->json([
                'error' => false,
                'message' => 'Get product success',
                'data' =>  $products
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, string $productType)
    {
        try {
            if ($productType == 'vps') {
                $validator = Validator::make($request->all(), ([
                    'name' => 'required|string',
                    'cpu' => 'required|integer',
                    'ram' => 'required|integer',
                    'storage' => 'required|integer',
                    'bandwidth' => 'required|integer',
                    'tagline' => 'required|string',
                    'description' => 'required|string',
                    'isFeatured' => 'required|boolean',
                    'displayOrder' => 'required|integer',
                    'vpsFeatures' => 'required|array',
                    'vpsFeatures.*.content' => 'required|string',
                    'vpsFeatures.*.highlight' => 'required|boolean',
                    'vpsFeatures.*.displayOrder' => 'required|integer',
                    'vpsPricings' => 'required|array',
                    'vpsPricings.*.durationMonths' => 'required|integer',
                    'vpsPricings.*.basePrice' => 'required|integer',
                    'vpsPricings.*.discountPercent' => 'required|integer'
                ]));

                if ($validator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validator->errors()->all()[0]
                    ], 422);
                }

                $create = $this->vps->createVpsPlan(
                    $request->name,
                    $request->cpu,
                    $request->ram,
                    $request->storage,
                    $request->bandwidth,
                    $request->tagline,
                    $request->description,
                    $request->isFeatured,
                    $request->displayOrder,
                    $request->vpsFeatures,
                    $request->vpsPricings
                );
            }

            return response()->json([
                'error' => false,
                'message' => 'Create service success',
                'data' =>  $create
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $productType, string $productId)
    {
        try {
            if ($productType == 'vps') {
                $validator = Validator::make($request->all(), ([
                    'name' => 'required|string',
                    'cpu' => 'required|integer',
                    'ram' => 'required|integer',
                    'storage' => 'required|integer',
                    'bandwidth' => 'required|integer',
                    'tagline' => 'required|string',
                    'description' => 'required|string',
                    'isFeatured' => 'required|boolean',
                    'displayOrder' => 'required|integer',
                    'vpsFeatures' => 'required|array',
                    'vpsFeatures.*.content' => 'required|string',
                    'vpsFeatures.*.highlight' => 'required|boolean',
                    'vpsFeatures.*.displayOrder' => 'required|integer',
                    'vpsPricings' => 'required|array',
                    'vpsPricings.*.durationMonths' => 'required|integer',
                    'vpsPricings.*.basePrice' => 'required|integer',
                    'vpsPricings.*.discountPercent' => 'required|integer'
                ]));

                if ($validator->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validator->errors()->all()[0]
                    ], 422);
                }

                $update = $this->vps->updateVpsPlan(
                    $productId,
                    $request->name,
                    $request->cpu,
                    $request->ram,
                    $request->storage,
                    $request->bandwidth,
                    $request->tagline,
                    $request->description,
                    $request->isFeatured,
                    $request->displayOrder,
                    $request->vpsFeatures,
                    $request->vpsPricings
                );
            }

            return response()->json([
                'error' => false,
                'message' => 'Update product success',
                'data' =>  $update
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
