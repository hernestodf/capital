<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Auth\Rbac;
use App\Repository\UserRepository;
use App\Repository\EventoRepository;
use App\Repository\ContasPagarRepository;
use App\Repository\DashboardRepository;
use App\Repository\ColaboradorRepository;
use App\Repository\MontagemRepository;
use App\Repository\ProdutoEventoRepository;

/**
 * Dashboard Controller - Redireciona para o dashboard correto baseado no perfil
 */
class DashboardController extends Controller
{
    private DashboardRepository $dashboardRepo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->dashboardRepo = new DashboardRepository();
    }

    /**
     * Redireciona para o dashboard correto baseado no perfil
     */
    public function index(): Response
    {
        $role = Rbac::getCurrentRole();

        return match($role) {
            'administrativo' => $this->administrativo(),
            'comercial' => $this->comercial(),
            'estoquista' => $this->estoquista(),
            default => \App\Core\Application::getInstance()->redirect('/auth/login')
        };
    }

    /**
     * Dashboard do Administrador
     */
    public function administrativo(): Response
    {
        // Verificar permissão
        if (!Rbac::check('dashboard.administrativo')) {
            return \App\Core\Application::getInstance()->redirect('/');
        }

        $userRepo = new UserRepository();
        $eventoRepo = new EventoRepository();
        $contasRepo = new ContasPagarRepository();
        $colaboradorRepo = new ColaboradorRepository();
        $montagemRepo = new MontagemRepository();

        // Estatísticas reais do banco
        $today = date('Y-m-d');
        $thisWeek = date('Y-m-d', strtotime('+7 days'));
        $currentMonth = date('m');
        $currentYear = date('Y');

        $stats = [
            // Usuários e colaboradores
            'total_users' => $userRepo->count(),
            'colaboradores_ativos' => $colaboradorRepo->countByStatus()['ativos'] ?? 0,
            
            // Eventos
            'eventos_hoje' => $eventoRepo->countByDateRange($today, $today),
            'eventos_semana' => $eventoRepo->countByDateRange($today, $thisWeek),
            
            // Financeiro
            'contas_vencidas' => count($contasRepo->getVencidas()),
            'contas_pendentes_mes' => $this->countContasPendentesMes($contasRepo, $currentMonth, $currentYear),
            'total_pago_mes' => $this->getTotalPagoMes($contasRepo, $currentMonth, $currentYear),

            // Montagens
            'montagens_pendentes' => $montagemRepo->countByStatus('pendente'),
            'devolucoes_pendentes' => $montagemRepo->countByStatus('iniciada'),
            
            // Colaboradores alocados hoje
            'colaboradores_alocados_hoje' => $eventoRepo->countColaboradoresAlocadosHoje($today),
        ];

        // Usuários recentes
        $recentUsers = $userRepo->getRecent(5);

        // Próximos eventos
        $proximosEventos = $eventoRepo->getProximos(5);

        // Contas próximo vencimento
        $contasProxVencimento = $contasRepo->getProximasVencimento(5);

        return $this->view('dashboard/administrativo', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'proximosEventos' => $proximosEventos,
            'contasProxVencimento' => $contasProxVencimento,
        ]);
    }

    /**
     * Helper: Conta contas pendentes do mês
     */
    private function countContasPendentesMes(ContasPagarRepository $repo, string $mes, string $ano): int
    {
        return $this->dashboardRepo->countContasPendentesMes($mes, $ano);
    }

    /**
     * Helper: Total pago no mês
     */
    private function getTotalPagoMes(ContasPagarRepository $repo, string $mes, string $ano): float
    {
        return $this->dashboardRepo->getTotalPagoMes($mes, $ano);
    }

    /**
     * Helper: Conta montagens concluídas hoje (devolvidas)
     */
    private function countMontagensConcluidasHoje(MontagemRepository $repo): int
    {
        return $this->dashboardRepo->countMontagensConcluidasHoje();
    }

    /**
     * Helper: Busca montagens pendentes
     */
    private function getMontagensPendentes(MontagemRepository $repo): array
    {
        return $this->dashboardRepo->getMontagensPendentes();
    }

    /**
     * Helper: Busca devoluções pendentes
     */
    private function getDevolucoesPendentes(MontagemRepository $repo): array
    {
        return $this->dashboardRepo->getDevolucoesPendentes();
    }

    /**
     * Dashboard do Produtor
     */
    public function comercial(): Response
    {
        // Verificar permissão
        if (!Rbac::check('dashboard.comercial')) {
            return \App\Core\Application::getInstance()->redirect('/');
        }

        $eventoRepo = new EventoRepository();
        $montagemRepo = new MontagemRepository();
        $colaboradorRepo = new ColaboradorRepository();
        $presencaRepo = new \App\Repository\EventoColaboradorPresencaRepository();
        $produtoRepo = new ProdutoEventoRepository();

        // Estatísticas reais do banco
        $today = date('Y-m-d');
        $thisWeek = date('Y-m-d', strtotime('+7 days'));

        $stats = [
            // Eventos
            'eventos_hoje' => $eventoRepo->countByDateRange($today, $today),
            'eventos_semana' => $eventoRepo->countByDateRange($today, $thisWeek),
            
            // Montagens
            'montagens_pendentes' => $montagemRepo->countByStatus('pendente'),
            'montagens_em_andamento' => $montagemRepo->countByStatus('montado'),
            'montagens_concluidas_hoje' => $this->countMontagensConcluidasHoje($montagemRepo),
            'devolucoes_pendentes' => $montagemRepo->countDevolucoesPendentes(),
            'montagens_concluidas_hoje' => $this->countMontagensConcluidasHoje($montagemRepo),
            
            // Colaboradores
            'colaboradores_ativos' => $colaboradorRepo->countByStatus()['ativos'] ?? 0,
            'colaboradores_alocados_hoje' => $eventoRepo->countColaboradoresAlocadosHoje($today),
            
            // Produtos
            'produtos_ativos' => $produtoRepo->countByStatus(1),
        ];

        // Eventos hoje
        $eventosHoje = $eventoRepo->getProximos(10);

        // Montagens pendentes
        $montagensPendentes = $this->getMontagensPendentes($montagemRepo);

        // Devoluções pendentes
        $devolucoesPendentes = $this->getDevolucoesPendentes($montagemRepo);

        return $this->view('dashboard/comercial', [
            'stats' => $stats,
            'eventosHoje' => $eventosHoje,
            'montagensPendentes' => $montagensPendentes,
            'devolucoesPendentes' => $devolucoesPendentes
        ]);
    }

    /**
     * Dashboard do Estoquista
     */
    public function estoquista(): Response
    {
        // Verificar permissão
        if (!Rbac::check('dashboard.estoquista')) {
            return \App\Core\Application::getInstance()->redirect('/');
        }

        try {
            $produtoRepo = new ProdutoEventoRepository();
            $categoriaRepo = new \App\Repository\CategoriaRepository();

            // Estatísticas reais do banco
            $stats = [
                // Produtos
                'total_produtos' => $produtoRepo->count(),
                'produtos_ativos' => $produtoRepo->countByStatus(1),
                'total_seriais' => $this->getTotalSeriais(),
                'seriais_em_evento' => $this->getSeriaisEmEvento(),
                
                // Salas
                'total_salas' => $this->getTotalSalas(),
            ];

            // Produtos por seção
            $produtosPorSecao = $this->getProdutosPorSecao();

            // Últimos produtos cadastrados
            $ultimosProdutos = $this->getUltimosProdutos(10);

            // Salas com produtos
            $salasComProdutos = $this->getSalasComProdutos();

            return $this->view('dashboard/estoquista', [
                'stats' => $stats,
                'produtosPorSecao' => $produtosPorSecao,
                'ultimosProdutos' => $ultimosProdutos,
                'salasComProdutos' => $salasComProdutos
            ]);
        } catch (\Throwable $e) {
            \App\Core\Logger::error('Erro no dashboard estoquista: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->view('dashboard/estoquista', [
                'stats' => [
                    'total_produtos' => 0,
                    'produtos_ativos' => 0,
                    'total_seriais' => 0,
                    'seriais_em_evento' => 0,
                    'total_salas' => 0,
                ],
                'produtosPorSecao' => [],
                'ultimosProdutos' => [],
                'salasComProdutos' => []
            ]);
        }
    }

    /**
     * Helper: Total de seriais
     */
    private function getTotalSeriais(): int
    {
        return $this->dashboardRepo->getTotalSeriais();
    }

    /**
     * Helper: Seriais em evento (montados)
     */
    private function getSeriaisEmEvento(): int
    {
        return $this->dashboardRepo->getSeriaisEmEvento();
    }

    /**
     * Helper: Total de salas
     */
    private function getTotalSalas(): int
    {
        return $this->dashboardRepo->getTotalSalas();
    }

    /**
     * Helper: Produtos por seção
     */
    private function getProdutosPorSecao(): array
    {
        return $this->dashboardRepo->getProdutosPorSecao();
    }

    /**
     * Helper: Últimos produtos cadastrados
     */
    private function getUltimosProdutos(int $limit = 10): array
    {
        return $this->dashboardRepo->getUltimosProdutos($limit);
    }

    /**
     * Helper: Salas com produtos
     */
    private function getSalasComProdutos(): array
    {
        return $this->dashboardRepo->getSalasComProdutos();
    }
}
