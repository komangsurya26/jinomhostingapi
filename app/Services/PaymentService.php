<?php

namespace App\Services;

use App\Models\Integrations\JinomPayment;
use App\Models\Order;

class PaymentService
{
    public function __construct(
        protected JinomPayment $jinomPayment,
    ) {}
    /**
     * Membuat payment baru
     *
     */
    public function createVaNumber(
        string $paymentMethod,
        string $transactionId,
        int $price,
        string $userName,
        string $userEmail,
        string $userPhone,
        string $productName
    ): object {
        try {
            $request = (object) [
                'paymentMethod' => $paymentMethod,
                'transactionId' => $transactionId,
                'price' => $price,
                'productName' => $productName,
                'name' => $userName,
                'email' => $userEmail,
                'phone' => $userPhone,
            ];

            $response = $this->jinomPayment->createTransaction($request);
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Membuat payment simulation
     * 
     * @param string $vaNumber
     */
    public function paymentSuccess(string $vaNumber)
    {
        $updated = Order::where('payment_code', $vaNumber)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid'
            ]);

        return $updated ? "Successfully paid" : "Already paid";
    }
}
