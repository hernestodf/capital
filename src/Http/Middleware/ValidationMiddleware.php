<?php

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;

class ValidationMiddleware implements MiddlewareInterface
{
    private array $rules;

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function handle(Request $request, callable $next): Response
    {
        $errors = $this->validate($request);

        if (!empty($errors)) {
            if ($this->isAjax($request)) {
                return Response::json([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Dados inválidos',
                ], 422);
            }

            // For non-AJAX, store errors in session and redirect back
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $request->all();
            return Response::redirect($request->server('HTTP_REFERER', '/'));
        }

        return $next($request);
    }

    private function validate(Request $request): array
    {
        $errors = [];

        foreach ($this->rules as $field => $rule) {
            $value = $request->input($field);
            $rules = explode('|', $rule);

            foreach ($rules as $r) {
                $error = $this->applyRule($field, $value, $r, $request);
                if ($error) {
                    $errors[$field][] = $error;
                    break; // Stop at first error per field
                }
            }
        }

        return $errors;
    }

    private function applyRule(string $field, $value, string $rule, Request $request): ?string
    {
        // Parse rule with parameters (e.g., min:3)
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        return match ($name) {
            'required' => empty($value) && $value !== '0' ? "O campo {$field} é obrigatório" : null,
            'email' => $value && !filter_var($value, FILTER_VALIDATE_EMAIL) ? "O campo {$field} deve ser um email válido" : null,
            'min' => $value && strlen($value) < (int)$param ? "O campo {$field} deve ter pelo menos {$param} caracteres" : null,
            'max' => $value && strlen($value) > (int)$param ? "O campo {$field} deve ter no máximo {$param} caracteres" : null,
            'numeric' => $value && !is_numeric($value) ? "O campo {$field} deve ser numérico" : null,
            'integer' => $value && !ctype_digit((string)$value) ? "O campo {$field} deve ser um número inteiro" : null,
            'alpha' => $value && !ctype_alpha($value) ? "O campo {$field} deve conter apenas letras" : null,
            'alpha_num' => $value && !ctype_alnum($value) ? "O campo {$field} deve conter apenas letras e números" : null,
            'url' => $value && !filter_var($value, FILTER_VALIDATE_URL) ? "O campo {$field} deve ser uma URL válida" : null,
            'date' => $value && !$this->isValidDate($value) ? "O campo {$field} deve ser uma data válida" : null,
            'in' => $value && $param && !in_array($value, explode(',', $param)) ? "O campo {$field} tem valor inválido" : null,
            'confirmed' => $value !== $request->input($field . '_confirmation') ? "O campo {$field} não confere" : null,
            'cpf' => $value && !$this->isValidCpf($value) ? "O campo {$field} deve ser um CPF válido" : null,
            'cnpj' => $value && !$this->isValidCnpj($value) ? "O campo {$field} deve ser um CNPJ válido" : null,
            default => null,
        };
    }

    private function isValidDate(string $value): bool
    {
        $formats = ['Y-m-d', 'd/m/Y', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $value);
            if ($d && $d->format($format) === $value) {
                return true;
            }
        }
        return false;
    }

    private function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $rest = ($sum * 10) % 11;
        if ($rest === 10) $rest = 0;
        if ($rest !== (int)$cpf[9]) return false;
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $rest = ($sum * 10) % 11;
        if ($rest === 10) $rest = 0;
        return $rest === (int)$cpf[10];
    }

    private function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        $weights = [6,5,4,3,2,9,8,7,6,5,4,3,2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i + 1];
        }
        $rest = $sum % 11;
        $digit1 = $rest < 2 ? 0 : 11 - $rest;
        if ((int)$cnpj[12] !== $digit1) return false;
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i];
        }
        $rest = $sum % 11;
        $digit2 = $rest < 2 ? 0 : 11 - $rest;
        return (int)$cnpj[13] === $digit2;
    }

    private function isAjax(Request $request): bool
    {
        return strtolower($request->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Create validation rules from a flat array.
     * 
     * Usage: ValidationMiddleware::rules([
     *     'nome' => 'required|min:3',
     *     'email' => 'required|email',
     * ])
     */
    public static function rules(array $rules): self
    {
        return new self($rules);
    }
}
