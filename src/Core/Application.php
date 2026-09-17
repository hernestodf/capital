<?php

namespace App\Core;

class Application
{
    private static ?Application $instance = null;
    private Router $router;
    private Request $request;
    private array $middleware = [];
    private bool $booted = false;
    private array $config = [];

    public function __construct()
    {
        self::$instance = $this;
        $this->router = new Router();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) return;
        
        Env::load();
        ErrorHandler::register();
        Debug::start();
        Session::start();
        
        // Carregar config/app.php
        $this->loadConfig();
        
        $this->booted = true;
    }
    
    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/app.php';
        
        if (file_exists($configFile)) {
            // Limpar cache do arquivo se foi modificado recentemente
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($configFile, true);
            }
            $this->config = require $configFile;
        }
    }
    
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? $default;
    }
    
    public function getTheme(): array
    {
        $themeFile = dirname(__DIR__, 2) . '/storage/theme.json';
        $configFile = dirname(__DIR__, 2) . '/config/app.php';

        // 1. Verificar storage/theme.json (override)
        $activeTheme = 'default';
        if (file_exists($themeFile)) {
            $data = json_decode(file_get_contents($themeFile), true);
            if (!empty($data['theme'])) {
                $activeTheme = $data['theme'];
            }
        }

        // 2. Fallback: ler do config/app.php
        if ($activeTheme === 'default' && file_exists($configFile)) {
            $content = file_get_contents($configFile);
            if (preg_match("/'active'\s*=>\s*'([^']+)'/", $content, $m)) {
                $activeTheme = $m[1];
            }
        }

        $themes = [
            'default' => [
                 'name'          => 'Cyan Neon',
    'primary'       => '#0E9AA7',
    'primary_glow'  => 'rgba(14,154,167,0.28)',
    'sidebar_bg'    => '#0C2D3F',
    'sidebar_hover' => 'rgba(14,154,167,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#CCFBF1',
    'background'    => '#F0FDFD',
    'text'          => '#0C4A6E',
    'text_light'    => '#0E9AA7',
            ],
            'pink' => [
                'name' => 'Rosa Chamativo',
                'primary' => '#E11D48',
                'primary_glow' => 'rgba(225,29,72,0.28)',
                'sidebar_bg' => '#1E1B4B',
                'sidebar_hover' => 'rgba(225,29,72,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FCE7F3',
                'background' => '#FDF2F8',
                'text' => '#1E1B4B',
                'text_light' => '#4C1D4E',
            ],
            'blue' => [
             'name'          => 'Blue Steel',
    'primary'       => '#2563EB',
    'primary_glow'  => 'rgba(37,99,235,0.35)',
    'sidebar_bg'    => '#13265C',
    'sidebar_hover' => 'rgba(59,130,246,0.4)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#DCE7FC',
    'background'    => '#F3F7FF',
    'text'          => '#13265C',
    'text_light'    => '#2F5AC7',
            ],
            'green' => [
                'name' => 'Green Nature',
                'primary' => '#059669',
                'primary_glow' => 'rgba(5,150,105,0.28)',
                'sidebar_bg' => '#064E3B',
                'sidebar_hover' => 'rgba(5,150,105,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#D1FAE5',
                'background' => '#ECFDF5',
                'text' => '#064E3B',
                'text_light' => '#059669',
            ],
            'amber' => [
                'name' => 'Âmbar Quente',
                'primary' => '#D97706',
                'primary_glow' => 'rgba(217,119,6,0.28)',
                'sidebar_bg' => '#1C1917',
                'sidebar_hover' => 'rgba(217,119,6,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FEF3C7',
                'background' => '#FFFBEB',
                'text' => '#451A03',
                'text_light' => '#92400E',
            ],
            'red' => [
                'name' => 'Vermelho Intenso',
                'primary' => '#DC2626',
                'primary_glow' => 'rgba(220,38,38,0.28)',
                'sidebar_bg' => '#1C0C0C',
                'sidebar_hover' => 'rgba(220,38,38,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FEE2E2',
                'background' => '#FEF2F2',
                'text' => '#450A0A',
                'text_light' => '#B91C1C',
            ],
            'slate' => [
                'name' => 'Cinza Elegante',
                'primary' => '#475569',
                'primary_glow' => 'rgba(71,85,105,0.28)',
                'sidebar_bg' => '#0F172A',
                'sidebar_hover' => 'rgba(71,85,105,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#F1F5F9',
                'background' => '#F8FAFC',
                'text' => '#1E293B',
                'text_light' => '#64748B',
            ],
            'indigo' => [
                'name' => 'Índigo Profundo',
                'primary' => '#4F46E5',
                'primary_glow' => 'rgba(79,70,229,0.28)',
                'sidebar_bg' => '#1E1B4B',
                'sidebar_hover' => 'rgba(79,70,229,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#E0E7FF',
                'background' => '#EEF2FF',
                'text' => '#1E1B4B',
                'text_light' => '#6366F1',
            ],
            'teal' => [
                'name' => 'Turquesa Oceano',
                'primary' => '#0D9488',
                'primary_glow' => 'rgba(13,148,136,0.28)',
                'sidebar_bg' => '#042F2E',
                'sidebar_hover' => 'rgba(13,148,136,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#CCFBF1',
                'background' => '#F0FDFA',
                'text' => '#134E4A',
                'text_light' => '#0D9488',
            ],
            'rose' => [
                'name' => 'Rosa Suave',
                'primary' => '#F43F5E',
                'primary_glow' => 'rgba(244,63,94,0.28)',
                'sidebar_bg' => '#1A0A0E',
                'sidebar_hover' => 'rgba(244,63,94,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#FFE4E6',
                'background' => '#FFF1F2',
                'text' => '#4C0519',
                'text_light' => '#E11D48',
            ],
            'emerald' => [
                'name' => 'Esmeralda Vibrante',
                'primary' => '#10B981',
                'primary_glow' => 'rgba(16,185,129,0.28)',
                'sidebar_bg' => '#022C22',
                'sidebar_hover' => 'rgba(16,185,129,0.4)',
                'sidebar_text' => 'rgba(255,255,255,0.75)',
                'surface' => '#A7F3D0',
                'background' => '#ECFDF5',
                'text' => '#064E3B',
                'text_light' => '#059669',
            ],
            'papiro' => [
    'name'          => 'Papiro',
    'primary'       => '#C2773A',
    'primary_glow'  => 'rgba(194,119,58,0.28)',
    'sidebar_bg'    => '#2A1506',
    'sidebar_hover' => 'rgba(194,119,58,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#F5E6D0',
    'background'    => '#FDF6EE',
    'text'          => '#3D1F08',
    'text_light'    => '#8B4C1A',
],
'forest' => [
    'name'          => 'Forest',
    'primary'       => '#3A7D44',
    'primary_glow'  => 'rgba(58,125,68,0.28)',
    'sidebar_bg'    => '#1A2E1E',
    'sidebar_hover' => 'rgba(58,125,68,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#D8EDD8',
    'background'    => '#F4F7F2',
    'text'          => '#1A3320',
    'text_light'    => '#255C2E',
],
'lavanda_real' => [
    'name'          => 'Lavanda Real',
    'primary'       => '#7C3AED',
    'primary_glow'  => 'rgba(124,58,237,0.28)',
    'sidebar_bg'    => '#1E1040',
    'sidebar_hover' => 'rgba(124,58,237,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#EDE0FF',
    'background'    => '#F8F4FF',
    'text'          => '#2E1065',
    'text_light'    => '#5B21B6',
],
'warm_sand' => [
    'name'          => 'Warm Sand',
    'primary'       => '#B08060',
    'primary_glow'  => 'rgba(176,128,96,0.28)',
    'sidebar_bg'    => '#1C1008',
    'sidebar_hover' => 'rgba(176,128,96,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#E8DCCF',
    'background'    => '#F5F0EB',
    'text'          => '#2C1A0E',
    'text_light'    => '#7A4E2A',
],
'arctic' => [
    'name'          => 'Arctic',
    'primary'       => '#0284C7',
    'primary_glow'  => 'rgba(2,132,199,0.28)',
    'sidebar_bg'    => '#071E2C',
    'sidebar_hover' => 'rgba(2,132,199,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#D4EEFA',
    'background'    => '#EFF9FF',
    'text'          => '#0C2A3D',
    'text_light'    => '#015A8A',
],
'dark' => [
    'name'          => 'Dark Violeta',
    'primary'       => '#8B5CF6',
    'primary_glow'  => 'rgba(139,92,246,0.28)',
    'sidebar_bg'    => '#0F0A1A',
    'sidebar_hover' => 'rgba(139,92,246,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#1E1533',
    'background'    => '#130E22',
    'text'          => '#E8E0F5',
    'text_light'    => '#A78BFA',
],
'midnight_mint' => [
    'name'          => 'Midnight Mint',
    'primary'       => '#34D399',
    'primary_glow'  => 'rgba(52,211,153,0.28)',
    'sidebar_bg'    => '#0A1F1A',
    'sidebar_hover' => 'rgba(52,211,153,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#132E28',
    'background'    => '#0B1F19',
    'text'          => '#D1FAE5',
    'text_light'    => '#6EE7B7',
],
'neon_orchid' => [
    'name'          => 'Neon Orchid',
    'primary'       => '#C084FC',
    'primary_glow'  => 'rgba(192,132,252,0.28)',
    'sidebar_bg'    => '#1A0A2E',
    'sidebar_hover' => 'rgba(192,132,252,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#2E1545',
    'background'    => '#1A0A2E',
    'text'          => '#F3E8FF',
    'text_light'    => '#D8B4FE',
],
'solar_forge' => [
    'name'          => 'Solar Forge',
    'primary'       => '#F97316',
    'primary_glow'  => 'rgba(249,115,22,0.28)',
    'sidebar_bg'    => '#1A0F05',
    'sidebar_hover' => 'rgba(249,115,22,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#2E1A0A',
    'background'    => '#1A0F05',
    'text'          => '#FFF7ED',
    'text_light'    => '#FB923C',
],
'ocean_deep' => [
    'name'          => 'Ocean Deep',
    'primary'       => '#06B6D4',
    'primary_glow'  => 'rgba(6,182,212,0.28)',
    'sidebar_bg'    => '#041C2C',
    'sidebar_hover' => 'rgba(6,182,212,0.35)',
    'sidebar_text'  => 'rgba(255,255,255,0.75)',
    'surface'       => '#0A2A3F',
    'background'    => '#041C2C',
    'text'          => '#ECFEFF',
    'text_light'    => '#22D3EE',
],
'neon_fire' => [
    'name'          => 'Neon Fire',
    'primary'       => '#FA870E',
    'primary_glow'  => 'rgba(250,135,14,0.35)',
    'sidebar_bg'    => '#1A0A02',
    'sidebar_hover' => 'rgba(250,135,14,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#2A1505',
    'background'    => '#1A0A02',
    'text'          => '#FFF7ED',
    'text_light'    => '#FB923C',
],
'neon_magenta' => [
    'name'          => 'Neon Magenta',
    'primary'       => '#D1108D',
    'primary_glow'  => 'rgba(209,16,141,0.35)',
    'sidebar_bg'    => '#1A0520',
    'sidebar_hover' => 'rgba(209,16,141,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#2A0A35',
    'background'    => '#1A0520',
    'text'          => '#FDF2F8',
    'text_light'    => '#F472B6',
],
'neon_red' => [
    'name'          => 'Neon Red',
    'primary'       => '#EF2922',
    'primary_glow'  => 'rgba(239,41,34,0.35)',
    'sidebar_bg'    => '#1A0505',
    'sidebar_hover' => 'rgba(239,41,34,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#2A0808',
    'background'    => '#1A0505',
    'text'          => '#FEF2F2',
    'text_light'    => '#F87171',
],
'neon_green' => [
    'name'          => 'Neon Green',
    'primary'       => '#11AA61',
    'primary_glow'  => 'rgba(17,170,97,0.35)',
    'sidebar_bg'    => '#051A0D',
    'sidebar_hover' => 'rgba(17,170,97,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#0A2A15',
    'background'    => '#051A0D',
    'text'          => '#ECFDF5',
    'text_light'    => '#34D399',
],
'neon_purple' => [
    'name'          => 'Neon Purple',
    'primary'       => '#8537E7',
    'primary_glow'  => 'rgba(133,55,231,0.35)',
    'sidebar_bg'    => '#100A20',
    'sidebar_hover' => 'rgba(133,55,231,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#1A0F30',
    'background'    => '#100A20',
    'text'          => '#F3E8FF',
    'text_light'    => '#A78BFA',
],
'neon_hotpink' => [
    'name'          => 'Neon Hot Pink',
    'primary'       => '#E11572',
    'primary_glow'  => 'rgba(225,21,114,0.35)',
    'sidebar_bg'    => '#1A0515',
    'sidebar_hover' => 'rgba(225,21,114,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#2A0A20',
    'background'    => '#1A0515',
    'text'          => '#FDF2F8',
    'text_light'    => '#F472B6',
],
'neon_blue' => [
    'name'          => 'Neon Blue',
    'primary'       => '#1A51F5',
    'primary_glow'  => 'rgba(26,81,245,0.35)',
    'sidebar_bg'    => '#050A1A',
    'sidebar_hover' => 'rgba(26,81,245,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#0A1530',
    'background'    => '#050A1A',
    'text'          => '#EFF6FF',
    'text_light'    => '#60A5FA',
],
'neon_magenta_dark' => [
    'name'          => 'Neon Magenta Dark',
    'primary'       => '#92063C',
    'primary_glow'  => 'rgba(146,6,60,0.35)',
    'sidebar_bg'    => '#15020A',
    'sidebar_hover' => 'rgba(146,6,60,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#200510',
    'background'    => '#15020A',
    'text'          => '#FDF2F8',
    'text_light'    => '#F43F5E',
],
'neon_forest' => [
    'name'          => 'Neon Forest',
    'primary'       => '#03731C',
    'primary_glow'  => 'rgba(3,115,28,0.35)',
    'sidebar_bg'    => '#021505',
    'sidebar_hover' => 'rgba(3,115,28,0.45)',
    'sidebar_text'  => 'rgba(255,255,255,0.85)',
    'surface'       => '#052010',
    'background'    => '#021505',
    'text'          => '#ECFDF5',
    'text_light'    => '#22C55E',
],
        ];

        return $themes[$activeTheme] ?? $themes['default'];
    }
    
    public function getAppName(): string
    {
        return $this->config['name'] ?? 'App';
    }
    
    public function getAppLogo(): string
    {
        return $this->config['logo'] ?? '';
    }
    
    public function getAppLogoText(): string
    {
        return $this->config['logo_text'] ?? 'A';
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function run(): void
    {
        $this->boot();
        
        // Registrar rotas ANTES de criar request
        $this->registerRoutes();

        $this->request = new Request();
        
        $response = $this->router->dispatch($this->request);
        $response->send();
    }

    public function useMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    protected function registerRoutes(): void
    {
    }

    public function isLocal(): bool
    {
        return Env::get('APP_ENV') === 'local';
    }

    public function baseUrl(): string
    {
        return Env::get('BASE_URL', '');
    }

    public function redirect(string $url): Response
    {
        // Adicionar baseUrl se URL não começar com http/https
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $baseUrl = rtrim($this->baseUrl(), '/');
            $url = $baseUrl . $url;
        }
        return Response::redirect($url);
    }

    public function view(string $view, array $data = [], int $statusCode = 200): Response
    {
        // Adicionar config automaticamente para todas as views
        $data['appName'] = $this->getAppName();
        $data['appLogo'] = $this->getAppLogo();
        $data['appLogoText'] = $this->getAppLogoText();
        $data['appTitle'] = $this->config['title'] ?? 'App';
        $data['appVersion'] = $this->config['version'] ?? '1.0.0';
        $data['theme'] = $this->getTheme();
        
        extract($data);
        
        $viewFile = dirname(__DIR__, 2) . "/views/$view.php";
        
        if (!file_exists($viewFile)) {
            return Response::json(['error' => 'View não encontrada: ' . $view], 404);
        }
        
        ob_start();
        include $viewFile;
        $html = ob_get_clean();
        
        return Response::html($html, $statusCode);
    }

    public function json(mixed $data): Response
    {
        return Response::json($data);
    }
}
