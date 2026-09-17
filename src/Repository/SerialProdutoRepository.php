<?php

namespace App\Repository;

use App\Database\Connection;

class SerialProdutoRepository extends BaseRepository
{
    protected string $table = 'seriaisproduto';
    protected array $fillable = ['id_produto', 'serial', 'status', 'motivo', 'status_devolucao'];

    public function findByProduto(int $idProduto): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT * FROM {$this->table} 
             WHERE id_produto = ? 
             ORDER BY id DESC"
        );
        $stmt->execute([$idProduto]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Buscar serial pelo numero (string)
     */
    public function findByNumero(string $serial): ?array
    {
        $stmt = Connection::get()->prepare(
            "SELECT s.*, p.produto as nome_produto 
             FROM {$this->table} s
             LEFT JOIN produtos p ON p.id = s.id_produto
             WHERE s.serial = ?
             LIMIT 1"
        );
        $stmt->execute([$serial]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateStatus(int $id, string $status, string $motivo = ''): bool
    {
        $sql = "UPDATE {$this->table} SET status = ?, motivo = ? WHERE id = ?";
        Connection::get()->prepare($sql)->execute([$status, $motivo, $id]);
        return true;
    }

    public function countByStatus(int $idProduto): array
    {
        $stmt = Connection::get()->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ATIVO' THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN status = 'MANUTENCAO' THEN 1 ELSE 0 END) as manutencao,
                SUM(CASE WHEN status = 'VENDER' THEN 1 ELSE 0 END) as vender
             FROM {$this->table} 
             WHERE id_produto = ?"
        );
        $stmt->execute([$idProduto]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Insert multiple serials in batch for a product
     * @param int $idProduto Product ID
     * @param array $serials Array of serial numbers
     * @param string $status Status (default: ATIVO)
     * @return array ['success' => int, 'duplicates' => int, 'errors' => array]
     */
    public function storeBatch(int $idProduto, array $serials, string $status = 'ATIVO'): array
    {
        $success = 0;
        $duplicates = 0;
        $errors = [];

        // Filter and sanitize serials
        $cleanSerials = [];
        foreach ($serials as $s) {
            $s = trim($s);
            if (!empty($s)) {
                $cleanSerials[] = $s;
            }
        }
        $cleanSerials = array_unique($cleanSerials);

        if (empty($cleanSerials)) {
            return [
                'success' => 0,
                'duplicates' => 0,
                'errors' => []
            ];
        }

        // Check for existing duplicates globally
        $placeholders = implode(',', array_fill(0, count($cleanSerials), '?'));
        $stmt = Connection::get()->prepare(
            "SELECT serial FROM {$this->table} WHERE serial IN ($placeholders)"
        );
        $stmt->execute($cleanSerials);
        $existing = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $toInsert = [];
        foreach ($cleanSerials as $s) {
            if (in_array($s, $existing)) {
                $duplicates++;
                $errors[] = sprintf('Código "%s" já existe em outro produto', $s);
            } else {
                $toInsert[] = $s;
            }
        }

        if (!empty($toInsert)) {
            try {
                // Build a bulk insert query: INSERT INTO table (id_produto, serial, status) VALUES (?, ?, ?), (?, ?, ?)
                $valuesSql = [];
                $params = [];
                foreach ($toInsert as $s) {
                    $valuesSql[] = "(?, ?, ?)";
                    $params[] = $idProduto;
                    $params[] = $s;
                    $params[] = $status;
                }
                
                $sql = "INSERT INTO {$this->table} (id_produto, serial, status) VALUES " . implode(', ', $valuesSql);
                $stmt = Connection::get()->prepare($sql);
                $stmt->execute($params);
                
                $success = count($toInsert);
            } catch (\Exception $e) {
                $errors[] = 'Erro ao salvar lote de códigos de barras: ' . $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ];
    }
}
