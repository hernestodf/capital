<?php

namespace App\Service;

use App\Database\Connection;
use Mpdf\Mpdf;

/**
 * Servico compartilhado para geracao de PDFs
 * 
 * Centraliza:
 * - Instanciacao do mPDF com configuracoes padrao
 * - Carregamento dos dados da empresa
 * - Geracao de header e footer padronizados
 * - Gerenciamento de diretorios temporarios
 */
class PdfGeneratorService
{
    private Mpdf $mpdf;
    private array $empresa;
    private string $tempDir;

    public function __construct()
    {
        $this->tempDir = dirname(__DIR__, 2) . '/tmp';
        
        // Criar diretorios temporarios
        if (!is_dir($this->tempDir . '/mpdf')) {
            mkdir($this->tempDir . '/mpdf', 0755, true);
        }
        if (!is_dir($this->tempDir . '/ttfonts')) {
            mkdir($this->tempDir . '/ttfonts', 0755, true);
        }

        // Inicializar mPDF com configuracoes padrao
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 30,
            'margin_bottom' => 20,
            'tempDir' => $this->tempDir,
        ]);

        // Carregar dados da empresa
        $this->empresa = $this->loadEmpresaData();
    }

    /**
     * Carrega dados da empresa do banco
     */
    private function loadEmpresaData(): array
    {
        try {
            $stmt = Connection::get()->query("SELECT * FROM empresa LIMIT 1");
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $data ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Retorna instancia do mPDF para configuracoes customizadas
     */
    public function getMpdf(): Mpdf
    {
        return $this->mpdf;
    }

    /**
     * Retorna dados da empresa
     */
    public function getEmpresa(): array
    {
        return $this->empresa;
    }

    /**
     * Gera header HTML padrao para todos os PDFs
     * 
     * Contem: logo, nome da empresa, CNPJ, cidade, telefone, timestamp
     */
    public function generateHeader(): string
    {
        $nome = $this->empresa['nome'] ?? 'SISLOC';
        $cnpj = $this->empresa['cnpj'] ?? '';
        $cidade = $this->empresa['cidade'] ?? '';
        $estado = $this->empresa['estado'] ?? '';
        $telefone = $this->empresa['telefone'] ?? '';
        $endereco = $this->empresa['endereco'] ?? '';
        $email = $this->empresa['email'] ?? '';
        $whatsapp = $this->empresa['whatsapp'] ?? '';
        $logoPath = $this->empresa['logo_path'] ?? '';

        $logoHtml = $this->buildLogoHtml($logoPath);
        $empresaLinha1 = $this->buildEmpresaInfoLine($cnpj, $cidade, $estado, $telefone);
        $empresaLinha2 = $this->buildEmpresaContatoLine($endereco, $email, $whatsapp);

        $html = '<table style="width:100%; border-bottom: 1px solid #1e293b; padding-bottom: 6px;">
            <tr>
                <td style="width:12%; vertical-align:middle; text-align:center;">';
        
        if (!empty($logoHtml)) {
            $html .= $logoHtml;
        }
        
        $html .= '</td>
                <td style="width:68%; vertical-align:middle; padding-left:8px;">
                    <span style="font-size:16px; font-weight:bold; color:#1e293b;">' . htmlspecialchars($nome) . '</span>';
        
        if (!empty($empresaLinha1)) {
            $html .= '<br><span style="font-size:9px; color:#475569;">' . htmlspecialchars($empresaLinha1) . '</span>';
        }
        if (!empty($empresaLinha2)) {
            $html .= '<br><span style="font-size:9px; color:#64748b;">' . htmlspecialchars($empresaLinha2) . '</span>';
        }
        
        $html .= '</td><td style="width:20%; text-align:right; vertical-align:middle;">
                    <span style="font-size:9px; color:#64748b;">' . date('d/m/Y H:i') . '</span>
                </td>
            </tr>
        </table>';

        return $html;
    }

    /**
     * Gera footer HTML padrao para todos os PDFs
     * 
     * Contem: texto customizado ou "Sistema SisLoc", numeracao de paginas
     */
    public function generateFooter(): string
    {
        $rodape = $this->empresa['rodape_pdf'] ?? '';
        return '<table style="width:100%; border-top: 1px solid #ddd; padding-top: 8px;">
            <tr>
                <td style="font-size:10px; color:#999; text-align:center;">' . htmlspecialchars($rodape ?: 'Sistema SisLoc - Todos os direitos reservados') . ' - Pagina {PAGENO}/{nbpg}</td>
            </tr>
        </table>';
    }

    /**
     * Configura header e footer no mPDF de uma vez
     */
    public function setupHeaderFooter(): void
    {
        $this->mpdf->SetHTMLHeader($this->generateHeader());
        $this->mpdf->SetHTMLFooter($this->generateFooter());
    }

    /**
     * Gera e faz download do PDF
     * 
     * @param string $html Conteudo HTML do documento
     * @param string $filename Nome do arquivo (sem extensao)
     */
    public function download(string $html, string $filename): void
    {
        $this->writeHtmlClean($html);
        $this->mpdf->Output($filename . '.pdf', 'D');
    }

    /**
     * Gera PDF e retorna como string (sem output nem exit)
     *
     * @param string $html Conteudo HTML do documento
     * @return string Conteudo binario do PDF
     */
    public function getPdfContent(string $html): string
    {
        $this->writeHtmlClean($html);
        return $this->mpdf->Output('', 'S');
    }

    /**
     * Gera PDF e exibe inline no navegador
     * 
     * @param string $html Conteudo HTML do documento
     * @param string $filename Nome do arquivo (sem extensao)
     */
    public function inline(string $html, string $filename): void
    {
        $this->writeHtmlClean($html);
        $this->mpdf->Output($filename . '.pdf', 'I');
    }

    /**
     * Escreve HTML removendo tags que conflitam com mPDF
     */
    private function writeHtmlClean(string $html): void
    {
        $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
        $html = preg_replace('/<html[^>]*>/i', '', $html);
        $html = preg_replace('/<\/html>/i', '', $html);
        $html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html);
        $html = preg_replace('/<\/?body[^>]*>/i', '', $html);
        $this->mpdf->WriteHTML($html);
    }

    /**
     * Retorna CSS base para documentos PDF
     */
    public function getBaseCss(): string
    {
        return '<style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; }
            h1 { font-size: 18px; color: #1e293b; margin: 0 0 6px 0; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
            h2 { font-size: 13px; color: #1e293b; margin: 0 0 10px 0; font-weight: bold; border-bottom: 2px solid #1e293b; padding-bottom: 3px; }
            h3 { font-size: 12px; color: #334155; margin: 0 0 8px 0; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; font-size: 10px; }
            th { background: #1e293b; color: #fff; font-weight: bold; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
            td { padding: 4px 8px; border-bottom: 1px solid #e5e7eb; color: #374151; }
            tr:nth-child(even) td { background: #f9fafb; }
            tr:hover td { background: #f3f4f6; }
            .label-cell { font-weight: bold; color: #6b7280; width: 100px; }
            .section-divider { margin: 16px 0 10px 0; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .text-muted { color: #9ca3af; }
        </style>';
    }

    /**
     * Build logo HTML if path exists
     */
    private function buildLogoHtml(string $logoPath): string
    {
        if (empty($logoPath)) {
            return '';
        }

        $basePath = dirname(__DIR__, 2) . '/public/';
        $fullPath = $basePath . $logoPath;
        
        if (!file_exists($fullPath)) {
            return '';
        }

        return '<img src="' . $fullPath . '" width="45" style="vertical-align:middle;margin-right:8px" />';
    }

    /**
     * Build empresa info line (CNPJ, cidade, telefone)
     */
    private function buildEmpresaInfoLine(string $cnpj, string $cidade, string $estado, string $telefone): string
    {
        $empresaDados = [];
        
        if (!empty($cnpj)) {
            $empresaDados[] = 'CNPJ: ' . $cnpj;
        }
        if (!empty($cidade)) {
            $empresaDados[] = $cidade . ($estado ? '/' . $estado : '');
        }
        if (!empty($telefone)) {
            $empresaDados[] = $telefone;
        }

        return !empty($empresaDados) ? implode(' | ', $empresaDados) : '';
    }

    /**
     * Build empresa contato line (endereco, email, whatsapp)
     */
    private function buildEmpresaContatoLine(string $endereco, string $email, string $whatsapp): string
    {
        $contatoDados = [];
        
        if (!empty($endereco)) {
            $contatoDados[] = $endereco;
        }
        if (!empty($email)) {
            $contatoDados[] = $email;
        }
        if (!empty($whatsapp)) {
            $contatoDados[] = 'WhatsApp: ' . $whatsapp;
        }

        return !empty($contatoDados) ? implode(' | ', $contatoDados) : '';
    }
}
