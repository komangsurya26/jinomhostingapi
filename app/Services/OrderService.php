<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\VpsDiscountCode;
use App\Models\VpsInstance;
use App\Models\VpsPlanPricing;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected VpsService $vpsService,
        protected PaymentService $paymentService,
    ) {}
    /**
     * Membuat order baru
     *
     * @param int $userId
     * @param int $totalAmount
     * @param string $productType
     * @param int $productId
     * @param int $planPricingId
     * @param string $promoCode
     * @param bool $isFree
     * @param string $vpsHostname
     * @param string $vpsRootPassword
     * @param int $vpsOsId
     * @param string $email
     * @param string $password
     * @param string $paymentMethod
     */
    public function createOrderVps(
        int $userId,
        int $price,
        string $productType,
        int $productId,
        int $planPricingId,
        ?string $promoCode,
        bool $isFree,
        string $vpsHostname,
        string $vpsRootPassword,
        int $vpsOsId,
        string $email,
        string $password,
        ?string $paymentMethod
    ) {
        DB::beginTransaction();
        try {
            $planPricing = VpsPlanPricing::with('vpsPlan')->find($planPricingId);

            $order = Order::create([
                'user_id' => $userId,
                'status' => $isFree ? 'free' : 'pending',
                'payment_method' => $paymentMethod,
            ]);

            // Membuat detail order
            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'product_type' => $productType,
                'product_id' => $productId,
                'plan_pricing_id' => $planPricingId,
                'price' => $price
            ]);

            // Jika tidak gratis
            if ($isFree === false) {
                if (!$paymentMethod) {
                    throw new \Exception('Payment method required');
                }
                // buat vps instance
                VpsInstance::create([
                    'user_id' => $userId,
                    'vps_plan_id' => $planPricing->vpsPlan->id,
                    'vps_os_id' => $vpsOsId,
                    'order_detail_id' => $orderDetail->id,
                    'duration_months' => $planPricing->duration_months,
                    'expired_at' => now()->addMonths($planPricing->duration_months),
                ]);

                //buat transaksi id
                $transactionId = 'VPS-' . time() . mt_rand(100000, 999999);

                // proses pembayaran jinom payment
                $payment = $this->paymentService->createVaNumber(
                    $paymentMethod,
                    $transactionId,
                    $price,
                    $order->user->name,
                    $order->user->email,
                    $order->user->phone,
                    $planPricing->vpsPlan->name
                );

                $paymentType = $payment->payment_type;
                $paymentCode = '';
                if ($paymentType === 'echannel') {
                    $paymentCode = $payment->bill_key;
                } elseif ($paymentType === 'cstore') {
                    $paymentCode = $payment->payment_code;
                } elseif ($paymentType === 'bank_transfer' && isset($payment->va_numbers[0]->va_number)) {
                    $paymentCode = $payment->va_numbers[0]->va_number;
                }

                $order->update([
                    'transaction_id' => $transactionId,
                    'payment_code' => $paymentCode,
                    'expired_at' => $payment->expired_at,
                    'total_price' => (int) $payment->gross_amount
                ]);
            }

            // Jika gratis
            if ($isFree === true) {
                // buat vps instance
                $vpsInstance = VpsInstance::create([
                    'user_id' => $userId,
                    'vps_plan_id' => $planPricing->vpsPlan->id,
                    'vps_os_id' => $vpsOsId,
                    'order_detail_id' => $orderDetail->id,
                    'duration_months' => $planPricing->duration_months,
                    'expired_at' => now()->addMonths($planPricing->duration_months),
                ]);

                // integrasi dengan virtualizor
                $this->vpsService->createVps(
                    $vpsInstance->vps_plan_id,
                    $vpsHostname,
                    $vpsRootPassword,
                    $vpsInstance->vpsOs->os_id,
                    $email,
                    $password
                );

                // update vps instance
                $vpsInstance->status = "active";
                $vpsInstance->save();
            }

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mengupdate status order
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $order = Order::findOrFail($orderId);
        $order->status = $status;
        return $order->save();
    }

    /**
     * Mendapatkan semua order untuk pengguna
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersUser(int $userId, ?string $status)
    {
        $query = Order::with([
            'orderDetails',
            'orderDetails.vpsPlanPricing',
            'orderDetails.vpsPlanPricing.vpsPlan',
        ])
            ->where('user_id', $userId);

        // Menambahkan kondisi jika status diberikan
        $query->when($status, function ($query) use ($status) {
            return $query->where('status', $status);
        });
        // kodisi lainnya

        $orders = $query->orderBy('created_at', 'desc')->get();

        return $orders;
    }


    /**
     * Mendapatkan semua order untuk pengguna
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrders(?string $status)
    {
        $query = Order::with([
            'orderDetails',
            'orderDetails.vpsPlanPricing',
            'orderDetails.vpsPlanPricing.vpsPlan',
        ]);
        // Menambahkan kondisi jika status diberikan
        $query->when($status, function ($query) use ($status) {
            return $query->where('status', $status);
        });
        // kodisi lainnya

        $orders = $query->orderBy('created_at', 'desc')->get();

        return $orders;
    }


    /**
     * Mengecek apakah VPS gratis.
     *
     * @param string $promoCode
     *
     * @return bool
     */
    public function isVpsFree(?string $promoCode): bool
    {
        if ($promoCode === null) {
            return false;
        }

        $promo = VpsDiscountCode::where('code', $promoCode)->first();

        return $promo && $promo->discount_percent === 100;
    }
}
