<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Service\EventoColaboradorService;

/**
 * Controller publico para registro de presenca de colaboradores
 * Nao requer autenticacao - validado por token
 */
class PresencaPublicController extends Controller
{
    private EventoColaboradorService $service;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->service = new EventoColaboradorService();
    }

    /**
     * Mostrar formulario de presenca (pagina do colaborador)
     */
    public function mostrarFormulario(string $token): Response
    {
        $alocacao = $this->service->validarTokenPresenca($token);
        if (!$alocacao) {
            return $this->view('presenca/erro', [
                'titulo' => 'Link Invalido',
                'mensagem' => 'Este link de presenca e invalido ou expirado.'
            ]);
        }

        // Pegar parametros da URL
        $data = $this->get('data', date('Y-m-d'));
        $tipo = $this->get('tipo', 'entrada');

        // Verificar presenca existente
        $presencaExistente = null;

        return $this->view('presenca/index', [
            'alocacao' => $alocacao,
            'data' => $data,
            'tipo' => $tipo,
            'token' => $token,
            'presencaExistente' => $presencaExistente
        ]);
    }

    /**
     * Processar registro de presenca (foto + geolocalizacao)
     */
    public function registrarPresenca(string $token): Response
    {
        $data = $this->post('data', date('Y-m-d'));
        $tipo = $this->post('tipo', 'entrada');
        $foto = $this->post('foto', '');
        $lat = (float)$this->post('lat', 0);
        $lng = (float)$this->post('lng', 0);

        if (empty($foto)) {
            return $this->json(['success' => false, 'error' => 'Foto e obrigatoria']);
        }
        if ($lat == 0 || $lng == 0) {
            return $this->json(['success' => false, 'error' => 'Permita o acesso a sua localizacao']);
        }

        // Salvar foto em arquivo
        $fotoPath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $foto, $matches)) {
            $ext = $matches[1];
            $ext = strtolower($ext);
            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
                return $this->json(["success" => false, "error" => "Formato de imagem nao permitido"]);
            }
            $foto = substr($foto, strpos($foto, ',') + 1);
            $foto = base64_decode($foto);

            if ($foto === false) {
                return $this->json(['success' => false, 'error' => 'Erro na foto']);
            }

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/presencas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0750, true);
            }

            $safeData = preg_replace('/[^a-zA-Z0-9_-]/', '', $data);
            $safeTipo = preg_replace('/[^a-zA-Z0-9_-]/', '', $tipo);
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) . '_' . $safeData . '_' . $safeTipo . '_' . time() . '.' . $ext;
            if (file_put_contents($uploadDir . $filename, $foto)) {
                $fotoPath = 'uploads/presencas/' . $filename;
            }
        }

        if (!$fotoPath) {
            return $this->json(['success' => false, 'error' => 'Erro ao salvar foto']);
        }

        $result = $this->service->registrarPresenca($token, $data, $tipo, $fotoPath, $lat, $lng);
        return $this->json($result);
    }

    /**
     * Mostrar página pública para confirmação de alocação de evento
     */
    public function confirmarAlocacaoPage(string $token): Response
    {
        $alocacao = $this->service->validarTokenPresenca($token);
        if (!$alocacao) {
            return $this->view('presenca/erro', [
                'titulo' => 'Link Inválido',
                'mensagem' => 'Este link de confirmação é inválido ou expirado.'
            ]);
        }

        return $this->view('presenca/confirmar_alocacao', [
            'alocacao' => $alocacao,
            'token' => $token
        ]);
    }

    /**
     * Processar ação de confirmação ou recusa
     */
    public function confirmarAlocacaoAction(string $token): Response
    {
        $alocacao = $this->service->validarTokenPresenca($token);
        if (!$alocacao) {
            return $this->json(['success' => false, 'error' => 'Link inválido ou expirado']);
        }

        $decisao = $this->post('decisao', ''); // 'C' para confirmar, 'R' para recusar
        if (!in_array($decisao, ['C', 'R'])) {
            return $this->json(['success' => false, 'error' => 'Decisão inválida']);
        }

        // Atualizar status de confirmação no banco
        $db = \App\Database\Connection::get();
        $stmt = $db->prepare("UPDATE evento_colaboradores SET confirmado = ? WHERE id = ?");
        $success = $stmt->execute([$decisao, $alocacao['id']]);

        if ($success) {
            $msg = $decisao === 'C' 
                ? 'Sua participação foi confirmada com sucesso! Nos dias do evento você receberá os links para registro de ponto por e-mail.'
                : 'Sua recusa foi registrada com sucesso. Agradecemos pelo retorno.';
            return $this->json(['success' => true, 'message' => $msg]);
        }

        return $this->json(['success' => false, 'error' => 'Erro ao registrar decisão no sistema']);
    }
}
