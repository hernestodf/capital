<?php

namespace App\Core;

/**
 * Component System - Gerenciador de Componentes do Design System
 *
 * Integra com os componentes da pasta docs/layout/branco/assets/components
 */
class Component
{
    private static ?Component $instance = null;
    private string $componentsPath;
    private array $loadedComponents = [];
    private array $registry = [];

    public function __construct()
    {
        $this->componentsPath = dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/';
        $this->loadRegistry();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Carrega o registry de componentes
     */
    private function loadRegistry(): void
    {
        $registryFile = dirname(__DIR__, 2) . '/docs/layout/branco/assets/registry.json';

        if (file_exists($registryFile)) {
            $content = file_get_contents($registryFile);
            $this->registry = json_decode($content, true) ?? [];
        }
    }

    /**
     * Renderiza um componente
     */
    public static function render(string $name, array $config = []): string
    {
        $instance = self::getInstance();
        return $instance->renderComponent($name, $config);
    }

    /**
     * Renderiza componente interno
     */
    private function renderComponent(string $name, array $config): string
    {
        // Sanitize: only allow alphanumeric, hyphens, and underscores
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            \App\Core\Logger::warning("Componente com nome invalido: {\$name}");
            return "<!-- Componente invalido -->";
        }

        $componentFile = $this->componentsPath . $name . '/' . $name . '.php';

        if (!file_exists($componentFile)) {
            \App\Core\Logger::warning("Componente não encontrado: {\$name}");
            return "<!-- Componente '{$name}' não encontrado -->";
        }

        // Carregar arquivo do componente se ainda não foi carregado
        if (!isset($this->loadedComponents[$name])) {
            require_once $componentFile;
            $this->loadedComponents[$name] = true;
        }

        // Nome da função de renderização
        $functionName = 'render' . $this->toPascalCase($name);

        if (!function_exists($functionName)) {
            \App\Core\Logger::error("Função de renderização não encontrada: {\$functionName}");
            return "<!-- Função '{$functionName}' não encontrada -->";
        }

        return $functionName($config);
    }

    /**
     * Verifica se componente existe
     */
    public static function exists(string $name): bool
    {
        $instance = self::getInstance();
        return file_exists($instance->componentsPath . $name . '/' . $name . '.php');
    }

    /**
     * Retorna lista de componentes disponíveis
     */
    public static function list(): array
    {
        $instance = self::getInstance();
        $components = [];

        if (isset($instance->registry['components'])) {
            foreach ($instance->registry['components'] as $name => $info) {
                $components[] = [
                    'name' => $name,
                    'description' => $info['uso'] ?? '',
                    'variants' => $info['variants'] ?? [],
                    'states' => $info['states'] ?? []
                ];
            }
        }

        return $components;
    }

    /**
     * Retorna informações de um componente
     */
    public static function info(string $name): ?array
    {
        $instance = self::getInstance();

        if (isset($instance->registry['components'][$name])) {
            return $instance->registry['components'][$name];
        }

        return null;
    }

    /**
     * Retorna guidelines para IA
     */
    public static function getAIGuidelines(): array
    {
        $instance = self::getInstance();
        return $instance->registry['ai-guidelines'] ?? [];
    }

    /**
     * Converte nome para PascalCase
     */
    private function toPascalCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * Renderiza múltiplos componentes
     */
    public static function renderMultiple(array $components): string
    {
        $output = '';

        foreach ($components as $component) {
            $name = $component['name'] ?? '';
            $config = $component['config'] ?? [];

            if (!empty($name)) {
                $output .= self::render($name, $config);
            }
        }

        return $output;
    }

    /**
     * Carrega CSS necessário para componentes
     */
    public static function styles(): string
    {
        $baseUrl = Application::getInstance()->baseUrl();
        return '<link rel="stylesheet" href="' . $baseUrl . '/docs/layout/branco/assets/css/design-system.css">';
    }

    /**
     * Carrega JS necessário para componentes
     */
    public static function scripts(): string
    {
        $baseUrl = Application::getInstance()->baseUrl();
        return '<script src="' . $baseUrl . '/docs/layout/branco/assets/js/design-system.js"></script>';
    }
}
