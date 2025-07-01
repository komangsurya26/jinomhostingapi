<?php

namespace App\Http\Controllers;

use App\Models\VpsInstance;
use App\Services\PromoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ServiceController extends Controller
{
    public function __construct(
        protected PromoService $promo
    ) {}

    /**
     * Update the specified resource in storage.
     */
    public function get(Request $request, string $productType)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string|in:active,suspended,terminated',
            ], [
                'status.in' => 'Status tidak valid, gunakan "active", "suspended", "terminated"',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            // get service
            $service = [];
            if ($productType == 'vps') {
                $service = VpsInstance::with('vpsPlan', 'vpsOs')
                    ->where('user_id', $user->id)
                    ->when($request->status, function ($query, $status) {
                        return $query->where('status', $status);
                    })
                    ->orderByDesc('created_at')
                    ->get();
            }

            return response()->json([
                'error' => false,
                'message' => 'Get service success',
                'data' =>  $service
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
