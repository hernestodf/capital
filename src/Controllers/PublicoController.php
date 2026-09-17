<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Service\ColaboradorService;
use App\Service\FornecedorService;
use App\Service\ColaboradorVerificacaoService;
use App\Service\EmailService;

/**
 * Controller para cadastros públicos vindos do site ProFox Network
 * 
 * Endpoints públicos (sem autenticação):
 *   POST /cadastro-colaborador
 *   POST /cadastro-fornecedor
 */
class PublicoController extends Controller
{
    private ColaboradorService $colaboradorService;
    private FornecedorService $fornecedorService;
    private ColaboradorVerificacaoService $verificacaoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->colaboradorService = new ColaboradorService();
        $this->fornecedorService = new FornecedorService();
        $this->verificacaoService = new ColaboradorVerificacaoService();
    }

    /**
     * POST /cadastro-colaborador
     * 
     * Recebe cadastro de colaborador do site ProFox Network
     */
    public function storeColaborador(): Response
    {
        try {
            // Validações básicas
            $nome = trim($this->post('nome') ?? '');
            $email = trim($this->post('email') ?? '');
            $telefone = trim($this->post('telefone') ?? '');
            $tipo = $this->post('tipo') ?? 'FREELANCE';

            if (empty($nome)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Campo nome é obrigatório'
                ], 400);
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Email válido é obrigatório'
                ], 400);
            }

            if (empty($telefone)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Telefone é obrigatório'
                ], 400);
            }

            if (!in_array($tipo, ['FUNCIONARIO', 'FREELANCE'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Tipo deve ser FUNCIONARIO ou FREELANCE'
                ], 400);
            }

            // Prepara dados para o service
            $data = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'tipo' => $tipo,
                'origem' => trim($this->post('origem') ?? 'Site ProFox'),
                'atua_como' => trim($this->post('classificacao') ?? $this->post('atua_como') ?? ''),
                'estado_para_trabalho' => trim($this->post('estado_trabalho') ?? $this->post('estado') ?? ''),
                'cep' => preg_replace('/\D/', '', $this->post('cep') ?? ''),
                'endereco' => trim($this->post('endereco') ?? ''),
                'bairro' => trim($this->post('bairro') ?? ''),
                'cidade' => trim($this->post('cidade') ?? ''),
                'estado' => trim($this->post('uf') ?? $this->post('estado') ?? ''),
                'cpf' => preg_replace('/\D/', '', $this->post('cpf') ?? ''),
                'tipo_chave_pix' => trim($this->post('tipo_chave_pix') ?? ''),
                'chavepix' => trim($this->post('chave_pix') ?? ''),
                'observacao' => trim($this->post('observacoes') ?? ''),
                'ativo' => 0,
            ];

            // Handle foto (base64)
            $foto = $this->post('foto');
            if (!empty($foto)) {
                if (strpos($foto, 'data:image') === 0) {
                    $parts = explode(',', $foto, 2);
                    $fotoBase64 = $parts[1] ?? $foto;
                } else {
                    $fotoBase64 = $foto;
                }
                
                $fotoSize = strlen(base64_decode($fotoBase64));
                if ($fotoSize > 5 * 1024 * 1024) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Foto muito grande. Máximo 5MB.'
                    ], 400);
                }

                $data['foto'] = $fotoBase64;
                $data['ativo'] = 1;
            }

            // Cria colaborador
            $id = $this->colaboradorService->create($data);

            // Se tem foto, criar registro de verificação
            if (!empty($data['foto'])) {
                try {
                    $this->verificacaoService->criarVerificacao($id, false);
                } catch (\Throwable $e) {
                    \App\Core\Logger::warning('Erro ao criar verificação: ' . $e->getMessage());
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Cadastro de colaborador enviado com sucesso! Entraremos em contato em breve.',
                'data' => [
                    'id' => $id,
                    'nome' => $nome
                ]
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            \App\Core\Logger::error('Erro no cadastro de colaborador: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Erro interno ao processar cadastro. Tente novamente.'
            ], 500);
        }
    }

    /**
     * POST /cadastro-fornecedor
     * 
     * Recebe cadastro de fornecedor do site ProFox Network
     */
    public function storeFornecedor(): Response
    {
        try {
            // Validações básicas
            $nomeFantasia = trim($this->post('nome') ?? '');
            $email = trim($this->post('email') ?? '');
            $telefone = trim($this->post('telefone') ?? '');
            $categoriaId = $this->post('categoria_id') ?? 0;

            if (empty($nomeFantasia)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Nome da empresa é obrigatório'
                ], 400);
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Email válido é obrigatório'
                ], 400);
            }

            if (empty($telefone)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Telefone é obrigatório'
                ], 400);
            }

            if (empty($categoriaId) || (int)$categoriaId === 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Categoria é obrigatória'
                ], 400);
            }

            // Prepara dados para o service
            $data = [
                'nome_fantasia'        => $nomeFantasia,
                'razao_social'         => $nomeFantasia,
                'cpf_cnpj'             => preg_replace('/\D/', '', $this->post('cnpj') ?? ''),
                'id_categoria'         => (int)$categoriaId,
                'id_subcategoria'      => !empty($this->post('subcategoria_id')) ? (int)$this->post('subcategoria_id') : null,
                // Identificação
                'inscricao_estadual'   => trim($this->post('inscricao_estadual') ?? '') ?: null,
                // Telefone principal da empresa (campo ao lado de subcategoria)
                'telefone'             => preg_replace('/\D/', '', $this->post('telefone') ?? '') ?: null,
                // Contato Financeiro linha 1
                'fin_nome'             => trim($this->post('responsavel') ?? $this->post('fin_nome') ?? '') ?: null,
                'fin_telefone'         => preg_replace('/\D/', '', $this->post('fin_telefone') ?? $telefone) ?: null,
                'fin_email'            => $email,
                // Contato Financeiro linha 2
                'fin_nome2'            => trim($this->post('fin_nome2') ?? '') ?: null,
                'fin_telefone2'        => preg_replace('/\D/', '', $this->post('fin_telefone2') ?? '') ?: null,
                'fin_email2'           => trim($this->post('fin_email2') ?? '') ?: null,
                // Contato Comercial linha 1
                'com_nome'             => trim($this->post('com_nome') ?? '') ?: null,
                'com_telefone'         => preg_replace('/\D/', '', $this->post('com_telefone') ?? '') ?: null,
                'com_email'            => trim($this->post('com_email') ?? '') ?: null,
                // Contato Comercial linha 2
                'com_nome2'            => trim($this->post('com_nome2') ?? '') ?: null,
                'com_telefone2'        => preg_replace('/\D/', '', $this->post('com_telefone2') ?? '') ?: null,
                'com_email2'           => trim($this->post('com_email2') ?? '') ?: null,
                // Presença Online
                'site'                 => trim($this->post('website') ?? $this->post('site') ?? '') ?: null,
                'instagram'            => trim($this->post('instagram') ?? '') ?: null,
                // Endereço
                'cep'                  => preg_replace('/\D/', '', $this->post('cep') ?? ''),
                'endereco'             => trim($this->post('endereco') ?? ''),
                'numero'               => '',
                'complemento'          => '',
                'bairro'               => trim($this->post('bairro') ?? ''),
                'cidade'               => trim($this->post('cidade') ?? ''),
                'estado'               => trim($this->post('uf') ?? $this->post('estado') ?? ''),
                'estado_para_trabalho' => trim($this->post('estado_trabalho') ?? ''),
                'observacao'           => trim($this->post('observacoes') ?? '') ?: null,
                'status'               => 1,
            ];

            // Valida CNPJ se fornecido
            if (!empty($data['cpf_cnpj'])) {
                $cnpjLimpo = $data['cpf_cnpj'];
                if (strlen($cnpjLimpo) !== 14 && strlen($cnpjLimpo) !== 11) {
                    return $this->json([
                        'success' => false,
                        'message' => 'CNPJ/CPF inválido'
                    ], 400);
                }
            }

            // Cria fornecedor
            $id = $this->fornecedorService->create($data);

            // Envia e-mail de boas-vindas (falha silenciosa — não bloqueia o cadastro)
            try {
                $this->enviarEmailBoasVindasFornecedor($email, $nomeFantasia);
            } catch (\Throwable $e) {
                \App\Core\Logger::warning('Falha ao enviar e-mail de boas-vindas para ' . $email . ': ' . $e->getMessage());
            }

            return $this->json([
                'success' => true,
                'message' => 'Cadastro de fornecedor enviado com sucesso! Entraremos em contato em breve.',
                'data' => [
                    'id' => $id,
                    'nome' => $nomeFantasia
                ]
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            \App\Core\Logger::error('Erro no cadastro de fornecedor: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Erro interno ao processar cadastro. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Envia e-mail de boas-vindas ao fornecedor recém-cadastrado.
     * Falha silenciosa — nunca impede o cadastro de ser salvo.
     */
    private function enviarEmailBoasVindasFornecedor(string $email, string $nomeFantasia): void
    {
        $emailService = new EmailService();

        $textPart = <<<TEXT
Olá, {$nomeFantasia}!

Seja bem-vindo à Capital!

É um prazer contar com você como fornecedor e parceiro em nossa rede de empresas parceiras.

Nosso objetivo é construir relações sólidas, transparentes e duradouras, baseadas em compromisso, profissionalismo, qualidade e confiança mútua.

Agradecemos por realizar seu cadastro em nossa plataforma e esperamos desenvolver grandes projetos juntos, contribuindo para entregas cada vez mais eficientes e de excelência.

Em breve, nossa equipe poderá entrar em contato para alinhamentos operacionais, oportunidades e futuras demandas.

Desejamos sucesso nessa parceria e reforçamos que a Capital permanece à disposição para quaisquer esclarecimentos.

Atenciosamente,
Capital
TEXT;

        $htmlPart = '<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

        <!-- Header -->
        <tr>
          <td style="background:#0c1a2e;border-radius:12px 12px 0 0;padding:32px 40px;text-align:center">
            <div style="font-size:26px;font-weight:700;color:#fff;letter-spacing:1px">PROFOX NETWORKS</div>
            <div style="font-size:13px;color:#94a3b8;margin-top:4px;letter-spacing:2px;text-transform:uppercase">Rede de Parceiros</div>
          </td>
        </tr>

        <!-- Faixa destaque -->
        <tr>
          <td style="background:#0ea5e9;padding:14px 40px;text-align:center">
            <div style="font-size:15px;font-weight:600;color:#fff;letter-spacing:.5px">✦ Bem-vindo ao nosso time de fornecedores ✦</div>
          </td>
        </tr>

        <!-- Corpo -->
        <tr>
          <td style="background:#ffffff;padding:40px 40px 32px;border-radius:0 0 12px 12px">

            <p style="font-size:17px;font-weight:600;color:#0c1a2e;margin:0 0 8px">Olá, ' . htmlspecialchars($nomeFantasia) . '!</p>

            <h2 style="font-size:22px;font-weight:700;color:#0ea5e9;margin:0 0 24px">Seja bem-vindo à Capital!</h2>

            <p style="font-size:15px;color:#334155;line-height:1.7;margin:0 0 16px">
              É um prazer contar com você como <strong>fornecedor e parceiro</strong> em nossa rede de empresas parceiras.
            </p>

            <p style="font-size:15px;color:#334155;line-height:1.7;margin:0 0 16px">
              Nosso objetivo é construir relações <strong>sólidas, transparentes e duradouras</strong>, baseadas em
              compromisso, profissionalismo, qualidade e confiança mútua.
            </p>

            <p style="font-size:15px;color:#334155;line-height:1.7;margin:0 0 16px">
              Agradecemos por realizar seu cadastro em nossa plataforma e esperamos desenvolver grandes projetos
              juntos, contribuindo para entregas cada vez mais eficientes e de excelência.
            </p>

            <!-- Destaque -->
            <div style="background:#f0f9ff;border-left:4px solid #0ea5e9;border-radius:4px;padding:16px 20px;margin:24px 0">
              <p style="font-size:14px;color:#0c4a6e;margin:0;line-height:1.6">
                📋 <strong>Próximos passos:</strong> Em breve, nossa equipe poderá entrar em contato para
                alinhamentos operacionais, oportunidades e futuras demandas.
              </p>
            </div>

            <p style="font-size:15px;color:#334155;line-height:1.7;margin:0 0 32px">
              Desejamos muito sucesso nessa parceria e reforçamos que a <strong>Capital</strong>
              permanece à disposição para quaisquer esclarecimentos.
            </p>

            <!-- Assinatura -->
            <div style="border-top:1px solid #e2e8f0;padding-top:24px">
              <p style="font-size:14px;color:#64748b;margin:0 0 4px">Atenciosamente,</p>
              <p style="font-size:16px;font-weight:700;color:#0c1a2e;margin:0">Capital</p>
              <p style="font-size:13px;color:#94a3b8;margin:4px 0 0">
                <a href="https://capital.sisloc.online" style="color:#0ea5e9;text-decoration:none">capital.sisloc.online</a>
              </p>
            </div>

          </td>
        </tr>

        <!-- Rodapé -->
        <tr>
          <td style="padding:20px 40px;text-align:center">
            <p style="font-size:12px;color:#94a3b8;margin:0;line-height:1.6">
              Este e-mail foi enviado automaticamente após o seu cadastro como fornecedor.<br>
              Capital — Produtora e Gestora de Eventos
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';

        $emailService->enviarMensagem(
            $email,
            $nomeFantasia,
            '',
            'Seja bem-vindo à Capital! 🤝',
            $htmlPart
        );
    }
}
