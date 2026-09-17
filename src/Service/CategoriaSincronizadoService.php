<?php

namespace App\Service;

/**
 * Serviço de Categorias/Subcategorias/Funções do Capital.
 *
 * Fonte de dados: banco local (tabelas categorias/subcategorias/funcoes).
 * Historicamente este serviço consumia uma API externa da ProFox — removido
 * porque o Capital é um projeto de outro cliente, sem relação com a ProFox.
 */
class CategoriaSincronizadoService
{
    // ──────────────────────────────────────────────────────────────────────────
    // CATEGORIAS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Busca categorias ativas no banco local.
     * Retorna [{id, categoria, status}] — compatível com todas as views do SisLoc.
     */
    public function getAtivas(): array
    {
        $cached = $this->getFromCache('categorias_ativas');
        if ($cached !== null) return $cached;

        $data = $this->fallbackCategorias();
        $this->saveToCache('categorias_ativas', $data, 300);
        return $data;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SUBCATEGORIAS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Busca subcategorias de uma categoria.
     * Retorna [{id, id_categoria, subcategoria, status}]
     */
    public function getSubcategorias(int $idCategoria): array
    {
        $key    = "subcategorias_{$idCategoria}";
        $cached = $this->getFromCache($key);
        if ($cached !== null) return $cached;

        $data = $this->fallbackSubcategorias($idCategoria);
        $this->saveToCache($key, $data, 600);
        return $data;
    }

    /**
     * Alias com fallback garantido (nunca lança exceção).
     */
    public function getSubcategoriasWithFallback(int $idCategoria): array
    {
        return $this->getSubcategorias($idCategoria);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FUNÇÕES / ATUA_COMO
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lista de funções para o campo atua_como de colaboradores.
     * Retorna [{id, nome}]
     */
    public function getFuncoes(): array
    {
        $cached = $this->getFromCache('funcoes');
        if ($cached !== null) return $cached;

        $data = $this->fallbackFuncoes();
        $this->saveToCache('funcoes', $data, 300);
        return $data;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FONTE LOCAL
    // ──────────────────────────────────────────────────────────────────────────

    private function fallbackCategorias(): array
    {
        try {
            $repo = new \App\Repository\CategoriaRepository();
            return array_map(function ($c) {
                $name = $c['categoria'] ?? '';
                return [
                    'id'        => (int)$c['id'],
                    'nome'      => $name,   // campo esperado por PublicoCategoriaController
                    'categoria' => $name,   // campo esperado pelas views internas
                    'status'    => (int)($c['status'] ?? 1),
                ];
            }, $repo->findAll());
        } catch (\Exception $e) {
            return [];
        }
    }

    private function fallbackSubcategorias(int $idCategoria): array
    {
        try {
            $repo = new \App\Repository\SubcategoriaRepository();
            return array_map(function ($s) {
                $name = $s['subcategoria'] ?? '';
                return [
                    'id'           => (int)$s['id'],
                    'id_categoria' => (int)$s['id_categoria'],
                    'nome'         => $name,
                    'subcategoria' => $name,
                    'status'       => (int)($s['status'] ?? 1),
                ];
            }, $repo->findByCategoria($idCategoria));
        } catch (\Exception $e) {
            return [];
        }
    }

    private function fallbackFuncoes(): array
    {
        $defaults = [
            'Sonorização', 'Iluminação', 'LED / Painéis',
            'Transmissão / Streaming', 'Gravação / Filmagem',
            'Montagem / Estrutura', 'Logística', 'Produção Geral',
            'Operação de Câmera', 'Operação de Luz', 'Operação de Som', 'Outros',
        ];
        return array_map(function ($nome, $i) {
            return ['id' => $i + 1, 'nome' => $nome];
        }, $defaults, array_keys($defaults));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CACHE em arquivo (storage/framework/cache/)
    // ──────────────────────────────────────────────────────────────────────────

    private function getFromCache(string $key): ?array
    {
        $file = $this->cachePath($key);
        if (!file_exists($file)) return null;

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) return null;

        $ttl = $data['ttl'] ?? 300;
        if ((time() - ($data['time'] ?? 0)) > $ttl) {
            @unlink($file);
            return null;
        }

        return $data['data'];
    }

    private function saveToCache(string $key, array $data, int $ttl = 300): void
    {
        $file = $this->cachePath($key);
        file_put_contents($file, json_encode(
            ['time' => time(), 'ttl' => $ttl, 'data' => $data],
            JSON_UNESCAPED_UNICODE
        ));
    }

    private function cachePath(string $key): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/framework/cache';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir . '/' . md5('profox_api_' . $key) . '.cache';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INVALIDAÇÃO DE CACHE — chamado após qualquer CRUD local
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Limpa o cache de categorias ativas.
     * Chamar após create/update/delete de categoria.
     */
    public static function clearCacheAtivas(): void
    {
        $dir  = dirname(__DIR__, 2) . '/storage/framework/cache';
        $file = $dir . '/' . md5('profox_api_categorias_ativas') . '.cache';
        if (file_exists($file)) @unlink($file);
    }

    /**
     * Limpa o cache de subcategorias de uma categoria específica.
     * Chamar após create/update/delete de subcategoria.
     */
    public static function clearCacheSubcategorias(int $idCategoria): void
    {
        $dir  = dirname(__DIR__, 2) . '/storage/framework/cache';
        $file = $dir . '/' . md5('profox_api_subcategorias_' . $idCategoria) . '.cache';
        if (file_exists($file)) @unlink($file);
    }
}
