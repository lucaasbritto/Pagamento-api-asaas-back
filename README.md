
## Backend do Sistema para Pagamentos usando API Asaas
Este é Backend para integrar com a API do Asaas, uma plataforma de pagamentos. O sistema permite realizar operações como buscar clientes, criar clientes, criar cobranças, receber pagamentos via boleto, cartã de crédito e gera QR Codes para pagamentos via Pix. A integração é feita através de chamadas HTTP usando a biblioteca Guzzle.
O Frontend foi desenvolvido em Vue e é necessario para o funcionamento da interface do sistema.

## Requisitos
PHP 8.0 ou superior
Composer
Node.js e npm (opcional, para gerenciamento de dependências)
GuzzleHTTP


## Instalação BACKEND

1. Clone o repositório:   
        git clone https://github.com/lucaasbritto/perfect_pay.git

2. Acesse o diretório do projeto:
    cd seu-repositorio

3. Configure o arquivo .env:
    Renomeie o arquivo .env.example para .env e configure as variáveis de ambiente conforme necessário,

4. Insira sua chave Token do Asaas no arquivo .env:
    ASAAS_ACCESS_TOKEN = 'seu_token'

5. Gere a chave de aplicativo do Laravel:
    php artisan key:generate

7. Inicie o servidor local:
    php artisan serve



## Instalação FRONTEND

1. Clone o repositório:   
        git clone https://github.com/lucaasbritto/pay_front.git

2. Navegue até o diretório do projeto:
    cd seu-repositorio

3. Instale as dependências:
    npm install

4. Inicie o servidor
    npm run serve





## Configuração do GuzzleHTTP
O serviço AsaasService usa o GuzzleHTTP para fazer solicitações à API do Asaas. O cliente Guzzle é configurado no construtor do serviço para usar a URL base da API do Asaas e incluir o token de acesso necessário.



## ENDPOINT DA API


### Buscar Cliente

- **URL:** `GET /customers`
- **Descrição:** Busca um cliente na API do Asaas pelo CPF ou CNPJ fornecido
- **Parâmetros:** $customerId (string): CPF ou CNPJ do cliente.
- **Resposta:**    
    Array com os dados do cliente ou null em caso de erro.


### Criar Cliente

- **URL:** `POST /customers`
- **Descrição:** Cria um novo cliente na API do Asaas com os dados fornecidos.
- **Parâmetros:** $data (array): Dados do cliente para criação.
- **Resposta:**    
    Array com os dados do cliente criado ou uma mensagem de erro em caso de falha.


### Criar Cobrança

- **URL:** `POST /payments`
- **Descrição:** Cria uma nova cobrança na API do Asaas com os dados fornecidos.
- **Parâmetros:**$data (array): Dados da cobrança para criação.
- **Resposta:**    
    Array com os dados da cobrança criada ou uma mensagem de erro em caso de falha.


### Gerar Pix Qr Code

- **URL:** `POST /payments/{$paymentId}/pixQrCode`
- **Descrição:** Gera um QR Code para pagamento via Pix para uma cobrança existente.
- **Parâmetros:** $paymentId (string): ID da cobrança para gerar o QR Code.
- **Resposta:**    
   Array com os dados do QR Code gerado ou uma mensagem de erro em caso de falha.


### EXEMPLO DE USO

```php

    use App\Services\AsaasService;

    $asaasService = new AsaasService();

    // Buscar Cliente
    $client = $asaasService->getClient('12345678900');
    print_r($client);

    // Criar Cliente
    $newClientData = [
        'name' => 'João da Silva',
        'cpfCnpj' => '12345678900',
        'email' => 'joao.silva@example.com',
    ];
    $newClient = $asaasService->createClient($newClientData);
    print_r($newClient);

    // Criar Cobrança
    $paymentData = [
        'billingType' => 'BOLETO',
        'customer' => '12345678900',
        'value' => 100.00,
        'dueDate' => '2024-12-31',
    ];
    $payment = $asaasService->createPayment($paymentData);
    print_r($payment);

    // Gerar Pix QR Code
    $qrCode = $asaasService->getPixQrCode('payment_id_example');
    print_r($qrCode);

```


### Listar Usuários com Carteira

- **URL:** `GET /api/walletUsers`
- **Descrição:** Retorna a lista de usuários que possuem carteiras.
- **Resposta:**
    ```json
    [
        {
            "id": 1,
            "name": "João",
            "email": "joao@example.com"
        },
        {
            "id": 2,
            "name": "Maria",
            "email": "maria@example.com"
        }
    ]
    ```

### Criar Nova Carteira

- **URL:** `POST /api/wallet-create`
- **Descrição:** Cria uma nova carteira para o usuário logado.
- **Requisição:**
    ```json
    {
        "name": "Minha Nova Carteira",
        "initial_balance": 100
    }
    ```
- **Resposta:**
    ```json
    {
        "id": 3,
        "cod": "0003",
        "name": "Minha Nova Carteira",
        "balance": 100
    }
    ```
