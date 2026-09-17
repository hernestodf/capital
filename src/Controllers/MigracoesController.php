<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Database\Connection;
use App\Auth\Rbac;

class MigracoesController extends Controller
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    /**
     * Página para upload e execução de migrations manuais
     */
    public function executar(): Response
    {
        // Apenas administradores
        if (!Rbac::isAdministrativo()) {
            return $this->redirect(\App\Core\Env::get('BASE_URL') . '/dashboard');
        }

        $baseUrl = \App\Core\Env::get('BASE_URL');
        $csrfToken = \App\Core\Csrf::getToken();

        $mensagem = null;
        $tipoMensagem = null;
        $resultado = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedToken = $this->post('_csrf_token');
            if (!Csrf::validate($submittedToken)) {
                $mensagem = 'Token CSRF inválido';
                $tipoMensagem = 'error';
            } else {
                $content = '';
                $nomeArquivo = '';

                // 1. Execução direta de arquivo do servidor (botão "Executar Migration 029")
                if ($this->post('executar_arquivo')) {
                    $arquivoRelativo = trim($this->post('executar_arquivo'));
                    $caminhoCompleto = __DIR__ . '/../../database/migrations/' . basename($arquivoRelativo);

                    if (!file_exists($caminhoCompleto)) {
                        $mensagem = 'Arquivo de migration não encontrado no servidor.';
                        $tipoMensagem = 'error';
                    } else {
                        $content = file_get_contents($caminhoCompleto);
                        $nomeArquivo = basename($arquivoRelativo);
                    }
                }
                // 2. Upload manual de arquivo
                elseif (isset($_FILES['migration_file']) && $_FILES['migration_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['migration_file'];
                    $content = file_get_contents($file['tmp_name']);
                    $nomeArquivo = $file['name'];
                }

                if (!empty($content)) {
                    // Validação de segurança — bloqueia comandos perigosos
                    $dangerousPatterns = [
                        'DROP DATABASE', 'DROP TABLE', 'TRUNCATE',
                        'GRANT ', 'REVOKE ',
                        'CREATE USER', 'DROP USER', 'ALTER USER',
                        'LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE',
                        'CREATE TRIGGER', 'DROP TRIGGER',
                        'CREATE PROCEDURE', 'DROP PROCEDURE',
                        'CREATE FUNCTION', 'DROP FUNCTION',
                    ];
                    $blocked = false;
                    foreach ($dangerousPatterns as $pattern) {
                        if (stripos($content, $pattern) !== false) {
                            $blocked = true;
                            break;
                        }
                    }
                    if ($blocked) {
                        $mensagem = 'Arquivo contem comandos perigosos bloqueados por seguranca.';
                        $tipoMensagem = 'error';
                    } else {
                        try {
                            $db = Connection::get();
                            $db->beginTransaction();

                            // Remove comentários de linha única (-- ...)
                            $content = preg_replace('/--.*$/m', '', $content);

                            // Divide por ;
                            $raw = explode(';', $content);
                            $statements = [];
                            foreach ($raw as $s) {
                                $s = trim($s);
                                if (!empty($s)) {
                                    $statements[] = $s;
                                }
                            }

                            $executados = 0;
                            foreach ($statements as $sql) {
                                if (!empty(trim($sql))) {
                                    $db->exec($sql);
                                    $executados++;
                                }
                            }

                            $db->commit();

                            $mensagem = "Migration executada com sucesso! $executados statement(s) processado(s).";
                            $tipoMensagem = 'success';
                            $resultado = "Arquivo: " . htmlspecialchars($nomeArquivo) . "\n" .
                                        "Statements executados: $executados";
                        } catch (\Exception $e) {
                            if (isset($db) && $db->inTransaction()) {
                                $db->rollBack();
                            }
                            $mensagem = 'Erro ao executar migration: ' . $e->getMessage();
                            $tipoMensagem = 'error';
                        }
                    }
                } else {
                    $mensagem = 'Nenhum arquivo foi enviado ou selecionado.';
                    $tipoMensagem = 'error';
                }
            }
        }

        // Lista de migrations disponíveis no servidor (para botões diretos)
        $migrationsDisponiveis = [];
        $dir = __DIR__ . '/../../database/migrations/';
        if (is_dir($dir)) {
            $files = glob($dir . '*.sql');
            foreach ($files as $file) {
                $migrationsDisponiveis[] = basename($file);
            }
            rsort($migrationsDisponiveis); // mais recentes primeiro
        }

        return $this->view('migracoes/executar', [
            'title' => 'Executar Migration',
            'baseUrl' => $baseUrl,
            'csrfToken' => $csrfToken,
            'mensagem' => $mensagem,
            'tipoMensagem' => $tipoMensagem,
            'resultado' => $resultado,
            'migrationsDisponiveis' => $migrationsDisponiveis,
        ]);
    }
}
