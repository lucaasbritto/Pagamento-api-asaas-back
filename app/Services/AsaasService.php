<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AsaasService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://sandbox.asaas.com/api/v3/',
            'headers' => [
                'User-Agent' => '1',
                'accept' => 'application/json',
                'access_token' => env('ASAAS_ACCESS_TOKEN', 'default_token'),
            ],
        ]);
    }

    // Buscar Cliente
    public function getClient($customerId){
        try {
            $response = $this->client->request('GET', 'customers', [
                'query' => ['cpfCnpj' => $customerId]
            ]);

            if ($response->getStatusCode() !== 200) {
                \Log::error('Status inesperado ao buscar cliente: ' . $response->getStatusCode());
                return null;
            }

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['data'][0] ?? null;

        } catch (RequestException $e) {
            \Log::error('Erro ao buscar cliente: ' . $e->getMessage());
            return null;
        }
    }

    // Criar Cliente
    public function createClient($data){
        try {
            $response = $this->client->request('POST', 'customers', [
                'json' => $data,
            ]);
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            \Log::warning('Resposta inesperada ao criar cliente: ' . $response->getStatusCode());
            return [
                'status' => 'error',
                'message' => 'Resposta inesperada do servidor.',
            ];
        } catch (RequestException $e) {
            \Log::error('Erro ao criar cliente: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Não foi possível criar o cliente. Por favor, tente novamente mais tarde.'
            ];
        }
    }

    // Criar Cobrança
    public function createPayment($data){
        try {
            $response = $this->client->request('POST', 'payments', [
                'json' => $data
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $errorData = $errorResponse ? json_decode($errorResponse, true) : [];
            
            $errorMessages = isset($errorData['errors']) ? array_map(function($error) {
                return $error['description'] ?? 'Erro desconhecido';
            }, $errorData['errors']) : ['Erro desconhecido'];
            
            \Log::error('Erro ao criar pagamento: ' . implode(', ', $errorMessages));

            return [
                'status' => 'error',
                'messages' => $errorMessages
            ];
        }
    }

    // Gerar Pix QrCod
    public function getPixQrCode($paymentId){  
        try {
            $response = $this->client->request('GET', "payments/{$paymentId}/pixQrCode");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            \Log::error('Erro ao obter o QR Code do Pix: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Erro ao obter o QR Code do Pix.'
            ];
        }
    }
}