<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class PaymentCallbackController extends Controller
{
    protected $api_token;

    public function __construct(
        protected PaymentService $payment
    ) {
        $this->api_token = config('services.payment.key');
    }

    public function callback()
    {
        $body = file_get_contents('php://input');
        $notification = json_decode($body, true);

        // API Key from Jinom Payment Gateway
        $string_key =  $this->api_token . $notification['order_id'] . $notification['transaction_status'];
        $signature = hash("sha256", $string_key);

        $orderId = $notification['order_id'];
        $grossAmount = $notification['gross_amount'];
        $paymentType = $notification['payment_type'];
        $transactionStatus = $notification['transaction_status'];
        $expiredAt = $notification['expired_at'];

        if ($signature !== $notification['signature']) {
            return response()->json([
                'error' => true,
                'status' => 400,
                'message' => 'Signature not match',
            ], 400);
        }

        // Start DB transaction for order updates
        DB::beginTransaction();

        try {
            if ($transactionStatus === "settlement") {
                // Payment success
                Order::where('transaction_id', $orderId)->update([
                    'status' => 'paid',
                ]);
            } elseif ($transactionStatus === "expired") {
                // Handle expired orders
                Order::where('transaction_id', $orderId)->update([
                    'status' => 'expired',
                ]);
            }

            DB::commit();
            return response()->json([
                'error' => false,
                'status' => 200,
                'message' => 'Success callback',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'status' => 500,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function simulation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ([
                'vaNumber' => 'required|string|exists:orders,payment_code',
            ]));

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'status' => 422,
                    'message' => $validator->errors()
                ], 422);
            }

            $vaNumber = $request->vaNumber;

            $response = $this->payment->paymentSuccess($vaNumber);

            return response()->json([
                'error' => false,
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
