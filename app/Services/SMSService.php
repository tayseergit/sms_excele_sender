<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SMSService
{
     
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct(Client $client)   // ← الحقن التلقائي
    {
        $this->client = $client;

        // استدعاء القيم من config()
        $this->apiKey = config('services.traccar.token');
        $this->apiUrl = config('services.traccar.url');
    }

    public function sendSMS($phone, $message)
    {
        $requestBody = [
            'message' => sprintf('%s', $message ),
            'to' => $phone,
        ];

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $requestBody,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
