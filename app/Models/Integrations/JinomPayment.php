<?php

namespace App\Models\Integrations;

use Illuminate\Database\Eloquent\Model;

class JinomPayment extends Model
{
    public function __construct()
    {
        // Set the API URL and token from environment variables
        $this->api_url = config('services.payment.url');
        $this->api_token = config('services.payment.key');
    }

    public function createTransaction($request)
    {
        try {
            $data = $this->prepareData($request);
            $jinom_payment_api = "{$this->api_url}/transaction/charge";
            $jinom_payment_token = $this->api_token;
            $options = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'header' => "Authorization: Bearer $jinom_payment_token\r\n" .
                        "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data),
                ],

            ];
            $context = stream_context_create($options);
            $response = file_get_contents($jinom_payment_api, false, $context);
            return json_decode($response);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function prepareData($request)
    {
        if ($request->paymentMethod === 'mandiri') {
            $paymentMethod =
                [
                    "payment_type" => "echannel",
                    "echannel" => [
                        "bill_info1" => "Payment:",
                        "bill_info2" => "Online purchase"
                    ]
                ];
        } elseif ($request->paymentMethod === 'alfamart') {
            $paymentMethod =
                [
                    "payment_type" => "cstore",
                    "cstore" => [
                        "store" => "alfamart"
                    ]
                ];
        } else {
            $paymentMethod =
                [
                    "payment_type" => "bank_transfer",
                    "bank_transfer" => [
                        "bank" => $request->paymentMethod
                    ]
                ];
        }
        $bodyRequest = array_merge(
            $paymentMethod,
            [
                'transaction_details' => [
                    'order_id' => $request->transactionId,
                    'gross_amount' => $request->price,
                ],
                'customer_details' => [
                    'fullname' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
                'item_details' => [
                    [
                        'name' => $request->productName,
                        'price' => $request->price,
                        'quantity' => 1,
                    ],
                ],
            ]
        );

        return $bodyRequest;
    }
}
