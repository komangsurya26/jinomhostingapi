<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Services\OrderService;

class OrderVpsController extends Controller
{
    public function __construct(
        protected OrderService $order,
    ) {}

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'productType' => 'required|string|in:vps,shared_hosting',
                'productId' => 'required|exists:vps_plans,id',
                'promoCode' => 'nullable|string',
                'planPricingId' => 'required|exists:vps_plan_pricings,id',
                'vpsHostname' => 'required|string|min:3|max:20|alpha_dash',
                'vpsPassword' => 'required|string|min:8|max:64|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                'price' => 'required|numeric|min:0',
                'vpsOsId' => 'required|exists:vps_os,id',
                'paymentMethod' => 'nullable|string|in:mandiri,bca,bri,bni,gopay,alfamart',  // Enum untuk payment method
            ], [
                'vpsHostname.required' => 'Hostname wajib diisi',
                'vpsHostname.min' => 'Hostname minimal 3 karakter',
                'vpsHostname.max' => 'Hostname maksimal 20 karakter',
                'vpsHostname.alpha_dash' => 'Hostname hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah',
                'vpsPassword.required' => 'Password wajib diisi',
                'vpsPassword.min' => 'Password minimal 8 karakter',
                'vpsPassword.max' => 'Password maksimal 64 karakter',
                'vpsPassword.regex' => 'Password harus mengandung minimal satu huruf besar, satu huruf kecil, satu angka, dan satu karakter khusus',
                'productId.required' => 'Product ID wajib diisi',
                'productId.exists' => 'Product ID tidak ditemukan dalam daftar VPS plans',
                'planPricingId.required' => 'Plan Pricing ID wajib diisi',
                'planPricingId.exists' => 'Plan Pricing ID tidak ditemukan dalam daftar VPS plan pricings',
                'productType.required' => 'Product type wajib diisi',
                'productType.in' => 'Product type tidak valid, gunakan "vps" atau "shared_hosting"',
                'price.required' => 'Price wajib diisi',
                'totalPrice.required' => 'Total price wajib diisi',
                'vpsOsId.required' => 'VPS OS ID wajib diisi',
                'vpsOsId.exists' => 'VPS OS ID tidak ditemukan dalam daftar VPS OS',
                'paymentMethod.in' => 'Payment method tidak valid, gunakan "mandiri", "bca", "bri", "bni", "gopay", atau "alfamart"',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()
                ], 422);
            }

            // create order
            if ($request->productType === 'vps') {
                // $isFree = true; //gratis
                // $isFree = false; //tidak gratis

                $isFree = $this->order->isVpsFree($request->promoCode);

                $createOrder = $this->order->createOrderVps(
                    $user->id,
                    $request->price,
                    $request->productType,
                    $request->productId,
                    $request->planPricingId,
                    $request->promoCode,
                    $isFree,
                    $request->vpsHostname,
                    $request->vpsPassword,
                    $request->vpsOsId,
                    $user->email,
                    $request->vpsPassword,
                    $request->paymentMethod
                );
            }

            return response()->json([
                'error' => false,
                'message' => 'Order berhasil dibuat',
                'data' => $createOrder
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
            $user = Auth::user();

            $validator = Validator::make($request->all(), ([
                'status' => 'nullable|string|in:pending,paid,cancelled,expired,free'
            ]));

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $status = $request->status;

            $orders = $this->order->getOrdersUser($user->id, $status);

            return response()->json([
                'error' => false,
                'message' => 'Get order success',
                'data' => $orders
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $orderId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:cancelled'
            ], [
                'status.required' => 'Status wajib diisi',
                'status.in' => 'Status tidak valid, gunakan "cancelled"'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->all()[0]
                ], 422);
            }

            $status = $request->status;

            $this->order->updateOrderStatus($orderId, $status);

            return response()->json([
                'error' => false,
                'message' => 'Update order success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
