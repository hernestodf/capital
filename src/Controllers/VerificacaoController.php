<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Service\ColaboradorVerificacaoService;

class VerificacaoController extends Controller
{
    private ColaboradorVerificacaoService $service;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ColaboradorVerificacaoService();
    }

    public function mostrarFormulario($token): Response
    {
        $data = $this->service->verificarToken($token);

        if (!$data) {
            return $this->view('verificacao/invalido', [
                'title' => 'Link Inválido',
            ]);
        }

        return $this->view('verificacao/formulario', [
            'title' => 'Confirmação de Cadastro',
            'token' => $token,
            'colaborador' => $data['colaborador'],
            'verificacao' => $data['verificacao'],
        ]);
    }

    public function processarVerificacao($token): Response
    {
        $foto = $this->post('foto');
        $lat = $this->post('lat');
        $lng = $this->post('lng');

        if (empty($foto)) {
            return $this->view('verificacao/formulario', [
                'title' => 'Confirmação de Cadastro',
                'token' => $token,
                'error' => 'Envie uma foto para confirmar seu cadastro',
            ]);
        }

        try {
            $this->service->processarVerificacao(
                $token,
                $foto,
                !empty($lat) ? (float) $lat : null,
                !empty($lng) ? (float) $lng : null
            );

            return $this->view('verificacao/sucesso', [
                'title' => 'Cadastro Confirmado',
            ]);
        } catch (\Throwable $e) {
            return $this->view('verificacao/formulario', [
                'title' => 'Confirmação de Cadastro',
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
