<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfValidation implements Rule
{
    
    public function __construct()
    {
        
    }
    
    public function passes($attribute, $value)
    {
        $cpf = preg_replace('/\D/', '', $value);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        $digits = substr($cpf, 0, 9);
        $checkDigits = substr($cpf, 9);

        for ($i = 0; $i < 2; $i++) {
            $sum = 0;
            for ($j = 0; $j < 9 + $i; $j++) {
                $sum += $digits[$j] * (10 + $i - $j);
            }
            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;
            if ($checkDigits[$i] != $digit) {
                return false;
            }
            $digits .= $digit;
        }

        return true;
    }
   
    public function message()
    {
        return 'O CPF informado é inválido.';
    }
}
