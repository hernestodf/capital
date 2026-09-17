<?php

namespace App\Service;

class TemaService
{
    private string $themeFile;
    private string $configFile;

    public function __construct()
    {
        $this->themeFile = dirname(__DIR__, 2) . '/storage/theme.json';
        $this->configFile = dirname(__DIR__, 2) . '/config/app.php';
    }

    public function getActiveTheme(): string
    {
        // 1. Verificar storage/theme.json (override)
        if (file_exists($this->themeFile)) {
            $data = json_decode(file_get_contents($this->themeFile), true);
            if (!empty($data['theme'])) {
                return $data['theme'];
            }
        }

        // 2. Fallback: ler do config/app.php
        $content = file_get_contents($this->configFile);
        if (preg_match("/'active'\s*=>\s*'([^']+)'/", $content, $m)) {
            return $m[1];
        }

        return 'default';
    }

    public function setActiveTheme(string $theme): void
    {
        $temasValidos = ['default', 'pink', 'blue', 'green', 'amber', 'red', 'slate', 'indigo', 'teal', 'rose', 'emerald', 'dark', 'papiro', 'forest', 'lavanda_real', 'warm_sand', 'arctic', 'midnight_mint', 'neon_orchid', 'solar_forge', 'ocean_deep', 'neon_fire', 'neon_magenta', 'neon_red', 'neon_green', 'neon_purple', 'neon_hotpink', 'neon_blue', 'neon_magenta_dark', 'neon_forest'];

        if (!in_array($theme, $temasValidos)) {
            throw new \InvalidArgumentException('Tema inválido');
        }

        file_put_contents($this->themeFile, json_encode(['theme' => $theme]));
    }
}
