<?php
/**
 * Script de Setup: Criar Estoque de Teste
 * Acesse: https://capital.sisloc.online/public/setup_estoque.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Conectar ao banco
$db = Database::getInstance();

echo "<h2>🔧 Setup Estoque - Criando Dados de Teste</h2>\n\n";

try {
    // 1. Criar Produtos
    echo "1️⃣  Criando produtos...\n";

    $produtos = [
        ['produto' => 'Cadeira Branca', 'custo' => 25.00, 'secao' => 1],
        ['produto' => 'Mesa Redonda', 'custo' => 80.00, 'secao' => 1],
        ['produto' => 'Tenda 6x6', 'custo' => 150.00, 'secao' => 2],
    ];

    foreach ($produtos as $p) {
        $db->query(
            "INSERT INTO produtos (produto, custo, id_secao, pode_ser_locado, status)
             VALUES (?, ?, ?, 'S', 1)
             ON DUPLICATE KEY UPDATE produto=produto",
            [$p['produto'], $p['custo'], $p['secao']]
        );
        echo "   ✅ {$p['produto']}\n";
    }

    // 2. Criar Seriais para Cadeira (100 unidades)
    echo "\n2️⃣  Criando 100 cadeiras (CAD001..CAD100)...\n";

    $cadeira = $db->query("SELECT id FROM produtos WHERE produto='Cadeira Branca' LIMIT 1")[0];
    $id_cadeira = $cadeira['id'];

    for ($i = 1; $i <= 100; $i++) {
        $serial = 'CAD' . str_pad($i, 3, '0', STR_PAD_LEFT);
        $db->query(
            "INSERT INTO seriaisproduto (id_produto, serial, status)
             VALUES (?, ?, 'ATIVO')
             ON DUPLICATE KEY UPDATE status='ATIVO'",
            [$id_cadeira, $serial]
        );
    }
    echo "   ✅ 100 cadeiras criadas (CAD001 até CAD100)\n";

    // 3. Criar Seriais para Mesa (15 unidades)
    echo "\n3️⃣  Criando 15 mesas (MESA01..MESA15)...\n";

    $mesa = $db->query("SELECT id FROM produtos WHERE produto='Mesa Redonda' LIMIT 1")[0];
    $id_mesa = $mesa['id'];

    for ($i = 1; $i <= 15; $i++) {
        $serial = 'MESA' . str_pad($i, 2, '0', STR_PAD_LEFT);
        $db->query(
            "INSERT INTO seriaisproduto (id_produto, serial, status)
             VALUES (?, ?, 'ATIVO')
             ON DUPLICATE KEY UPDATE status='ATIVO'",
            [$id_mesa, $serial]
        );
    }
    echo "   ✅ 15 mesas criadas (MESA01 até MESA15)\n";

    // 4. Criar Seriais para Tenda (5 unidades)
    echo "\n4️⃣  Criando 5 tendas (TENDA1..TENDA5)...\n";

    $tenda = $db->query("SELECT id FROM produtos WHERE produto='Tenda 6x6' LIMIT 1")[0];
    $id_tenda = $tenda['id'];

    for ($i = 1; $i <= 5; $i++) {
        $serial = 'TENDA' . $i;
        $db->query(
            "INSERT INTO seriaisproduto (id_produto, serial, status)
             VALUES (?, ?, 'ATIVO')
             ON DUPLICATE KEY UPDATE status='ATIVO'",
            [$id_tenda, $serial]
        );
    }
    echo "   ✅ 5 tendas criadas (TENDA1 até TENDA5)\n";

    // 5. Verificar dados criados
    echo "\n5️⃣  Verificando dados...\n";

    $total_produtos = $db->query("SELECT COUNT(*) as total FROM produtos")[0]['total'];
    $total_seriais = $db->query("SELECT COUNT(*) as total FROM seriaisproduto")[0]['total'];

    echo "   ✅ Total de produtos: $total_produtos\n";
    echo "   ✅ Total de seriais: $total_seriais\n";

    echo "\n✅ SETUP COMPLETO!\n\n";
    echo "Agora visite: https://capital.sisloc.online/public/estoque\n";
    echo "Você deve ver:\n";
    echo "  - Cadeira Branca (100 unidades)\n";
    echo "  - Mesa Redonda (15 unidades)\n";
    echo "  - Tenda 6x6 (5 unidades)\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>
<style>
body { font-family: monospace; background: #222; color: #0f0; padding: 20px; }
h2 { color: #0ff; }
</style>
