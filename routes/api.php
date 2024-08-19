<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagamentoController;

Route::post('/process-payment', [PagamentoController::class, 'processPayment']);
