<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SMSService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = "token";
        $this->apiUrl ="https://www.traccar.org/sms/";
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