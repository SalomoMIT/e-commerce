<?php

namespace App\Services;

use Config\Services;

class OngkirService
{
    public function __construct()
    {
        $this->baseUrl = rtrim($_ENV['ONGKIR_BASE_URL']);
        $this->CALCULATE_DELIVERY_COST = rtrim($_ENV['CALCULATE_DELIVERY_COST']);
        $this->apiKey  = $_ENV['ONGKIR_API_KEY'];
        $this->listCourier = [
            array("code"=>"jne","name"=>"JNE"),
            array("code"=>"sicepat","name"=>"SiCepat"),
            array("code"=>"ide","name"=>"IDExpress"),
            array("code"=>"sap","name"=>"SAP Express"),
            array("code"=>"ninja","name"=>"Ninja"),
            array("code"=>"jnt","name"=>"J&T Express"),
            array("code"=>"tiki","name"=>"TIKI"),
            array("code"=>"wahana","name"=>"Wahana Express"), 
            array("code"=>"pos","name"=>"POS Indonesia"),
            array("code"=>"sentral","name"=>"Sentral Cargo"),
            array("code"=>"lion","name"=>"Lion Parcel"),
            array("code"=>"rex","name"=>"Royal Express Asia"),
        ];
    }
    public function calculateShippingCost($origin, $destination, $weight, $courier)
    {
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->post(
                $this->CALCULATE_DELIVERY_COST,
                [
                    'headers' => [
                        'accept'       => 'application/json',
                        'content-type' => 'application/x-www-form-urlencoded',
                        'key'          => $this->apiKey,
                    ],
                    'form_params' => [
                        'origin'      => $origin,
                        'destination' => $destination,
                        'weight'      => $weight,
                        'courier'     => $courier,
                    ],
                ]
            );

            return json_decode($response->getBody(), true);

        } catch (\Throwable $e) {
            log_message('error', 'RajaOngkir Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function calculateOngkir(): array
    {
        $client = Services::curlrequest();

        $response = $client->get($this->CALCULATE_DELIVERY_COST, [
            'headers' => [
                'Accept' => 'application/json',
                'key'    => $this->apiKey,
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody(), true);
        }

        return [];
    }
    public function getProvinces(): array
    {
        $client = Services::curlrequest();

        $response = $client->get($this->baseUrl . '/province', [
            'headers' => [
                'Accept' => 'application/json',
                'key'    => $this->apiKey,
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody(), true);
        }

        return [];
    }
    public function getCities($provinceId)
    {
        $client = Services::curlrequest();

        try {
            $response = $client->get("{$this->baseUrl}/city/{$provinceId}", [
                'headers' => [
                    'accept' => 'application/json',
                    'key'    => $this->apiKey,
                ],
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }

            return [
                'error' => true,
                'message' => 'Request failed',
                'status' => $response->getStatusCode()
            ];

        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getDistrict($cityId)
    {
        $client = Services::curlrequest();

        try {
            $response = $client->get("{$this->baseUrl}/district/{$cityId}", [
                'headers' => [
                    'accept' => 'application/json',
                    'key'    => $this->apiKey,
                ],
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }

            return [
                'error' => true,
                'message' => 'Request failed',
                'status' => $response->getStatusCode()
            ];

        } catch (\Throwable $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
    
}