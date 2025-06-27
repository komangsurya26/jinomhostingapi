<?php

namespace App\Models\Integrations;

use Illuminate\Database\Eloquent\Model;

class JinomPelanggan extends Model
{
    public function __construct()
    {
        $this->api_url = config('services.notification.url');
        $this->api_token = config('services.notification.key');
    }

    public function sendNotifikasi($templateName, $dynamicVariables = [])
    {
        try {
            $variableDynamics = [];

            foreach ($dynamicVariables as $key => $value) {
                $variableDynamics[] = [
                    'name' => $key,
                    'value' => $value
                ];
            }

            $notificationData = [
                'broadcast_name' => "integrasi_with_api",
                'template_name' => $templateName,
                'dynamic_variables' => $variableDynamics
            ];

            $options = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'header' => "Authorization: Bearer " . $this->api_token . "\r\n" .
                        "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($notificationData),
                ],
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($this->api_url . '/broadcast/store', false, $context);
            return (object)[
                'error' => false,
                'message' => 'Notifikasi berhasil dikirim.',
            ];
        } catch (\Throwable $th) {
            return (object)[
                'error' => true,
                'message' => $th->getMessage(),
            ];
        }
    }
}
