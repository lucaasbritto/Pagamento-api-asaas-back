<?php

namespace App\Http\Controllers;

use App\Services\AsaasService;
use Illuminate\Http\Request;
use App\Rules\CpfValidation;

class PagamentoController extends Controller
{
    protected $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    public function processPayment(Request $request)
    { 
        // Pegar a data do dia
        $dateDay = date('Y-m-d'); 
        
        // Validando os dados principais
        $validated = $request->validate([
            'name' => 'required|string',
            'cpf' => ['required', 'string', new CpfValidation], //CpfValidation uma classe para validar CPF
            'email' => 'required|email',
            'payment_type' => 'required|string|in:pix,boleto,credit_card',
            'amount' => 'required|numeric',
        ]);
                
        $customerId = $request->cpf; // CPF ou CNPJ do cliente
        $client = $this->asaasService->getClient($customerId); // CONSULTAR SE CLIENTE EXISTE
        
        // Se nao existir cria um novo cliente
        if (!$client) {
             $clientData = [
                'name' => $request->name,
                'cpfCnpj' => $customerId,
                'email' => $request->email,
            ];
            
            try {
                $client = $this->asaasService->createClient($clientData);            
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erro ao criar cliente: ' . $e->getMessage()
                ], 500);
            }           
        } 
       
        // Dados para requisição
        $data = [
            'billingType' => $request->payment_type, // 'PIX', 'BOLETO', 'CREDIT_CARD'
            'customer' => $client['id'], // ID do cliente
            'value' => $request->amount, // Valor da cobrança
            'dueDate' => $dateDay, // Data de vencimento no formato YYYY-MM-DD
        ];

        // Se for Cartao de credito enviar os dados do cartão e titular
        if ($validated['payment_type'] === 'credit_card') {

            // Valida os dados do cartão
            $request->validate([
                'card_name' => 'required|string',
                'card_number' => 'required|numeric|digits:16',
                'expiry_date_month' => 'required|string|between:1,12|digits:2',
                'expiry_date_year' => 'required|digits:4|integer',
                'cvc' => 'required|numeric|digits:3', 
                'nameUserCard' => 'required|string',
                'emailUserCard' => 'required|email',
                'cpfUserCard' => ['required', 'string', new CpfValidation],                
                'cep' => 'required|string',
                'addressNumber' => 'required|string',
                'phone' => 'required|string',
            ]);

            // Estando validado ele armazena os dados
            $data['creditCard'] = [
                'holderName' => $request->card_name,
                'number' => $request->card_number,
                'expiryMonth' => $request->expiry_date_month,
                'expiryYear' => $request->expiry_date_year,
                'ccv' => $request->cvc,
            ];
            $data['creditCardHolderInfo'] = [
                'name' => $request->nameUserCard,
                'email' => $request->emailUserCard,
                'cpfCnpj' => $request->cpfUserCard,
                'postalCode' => $request->cep,
                'addressNumber' => $request->addressNumber,
                'phone' => $request->phone,
            ];
        }

        try{
            // Criar uma nova Cobrança
            $response = $this->asaasService->createPayment($data);

            
            // Se for Pix Gera um Qr Code
            if($request->payment_type == 'pix'){
                $response = $this->asaasService->getPixQrCode($response['id']);
            }

            
            // Se der erro de transaçao retorna os erros para exibir no front
            if (isset($response['status']) && $response['status'] === 'error') {
                return response()->json([
                    'status' => 'error',
                    'messages' => $response['messages']
                ], 400);
            }
        
            // Deu tudo certo retorna o resultado
            return response()->json([
                'status' => 'success',
                'data' => $response,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 500);
        }

    }
}
