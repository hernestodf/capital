<?php
/**
 * Script de migração da planilha Checklist SEBRAE MT.xlsx
 * - Extrai unidades de medida distintas
 * - Insere unidades na tabela unidademedida
 * - Insere itens na tabela planilhas
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Database\Connection;

// Carregar .env manualmente para o Env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[$key] = trim($value);
    }
}

echo "=== Migração da Planilha SEBRAE MT ===\n\n";

// Ler arquivo Excel
$xlsxFile = __DIR__ . '/../Checklist SEBRAE MT.xlsx';
if (!file_exists($xlsxFile)) {
    die("Erro: Arquivo não encontrado: $xlsxFile\n");
}

try {
    $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxFile);
} catch (Exception $e) {
    die("Erro ao ler planilha: " . $e->getMessage() . "\n");
}

// Ler dados da sheet "TR" (que tem os itens)
$sheetTR = $spreadsheet->getSheetByName('TR');
if (!$sheetTR) {
    die("Erro: Sheet 'TR' não encontrada\n");
}

// Extrair dados
$unidadesMedida = [];
$itens = [];
$itensUnidades = [];

echo "Lendo dados da planilha...\n";

// Pular cabeçalho (linha 0) e iterar pelas linhas
foreach ($sheetTR->getRowIterator(2) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    
    $linha = [];
    foreach ($cellIterator as $cell) {
        $linha[] = $cell->getValue();
    }
    
    // Colunas: A=ITEM, B=SERVIÇOS, C=DESCRIÇÃO, F=UNIDADE, H=VALOR UNID
    $itemNum = $linha[0] ?? null;  // Coluna A
    $servico = $linha[1] ?? null;  // Coluna B
    $descricao = $linha[2] ?? null; // Coluna C
    $unidade = $linha[5] ?? null;  // Coluna F
    $valor = $linha[7] ?? null;    // Coluna H
    
    // Ignorar linhas vazias ou cabeçalhos
    if (empty($servico) || empty($valor) || !is_numeric($valor)) {
        continue;
    }
    
    // Normalizar unidade: remover quebras de linha e múltiplos espaços
    $unidade = preg_replace('/\s+/', ' ', $unidade);
    $unidade = trim($unidade);
    
    // Capitalizar primeira letra de cada palavra (valor formatado para salvar)
    $unidadeFormatada = ucwords(strtolower($unidade));
    
    if (empty($unidadeFormatada)) {
        $unidadeFormatada = 'Unidade';
    }
    
    // Usar lowercase como chave para evitar duplicatas por case-insensitive
    $unidadeLower = strtolower($unidadeFormatada);
    $unidadesMedida[$unidadeLower] = $unidadeFormatada;
    $itensUnidades[] = $unidadeLower;
    
    // Montar item no formato "coluna B - coluna A"
    $itemNome = trim($servico . ' - ' . $itemNum);
    
    // Guardar unidade em lowercase para buscar depois
    $unidadeLower = strtolower($unidade);
    
    $itens[] = [
        'item' => $itemNome,
        'descricao' => $descricao ?? '',
        'unidade' => $unidadeLower,
        'valor' => (float) $valor
    ];
}

echo "Total de itens encontrados: " . count($itens) . "\n";
echo "Total de unidades de medida distintas: " . count($unidadesMedida) . "\n\n";

// Inserir unidades de medida
echo "Inserindo unidades de medida...\n";
$db = Connection::get();

// Primeiro, verifica quais já existem
$existentes = $db->query("SELECT unidademedida FROM unidademedida")->fetchAll(PDO::FETCH_COLUMN);
$existentesMap = array_map('strtolower', $existentes);

foreach (array_keys($unidadesMedida) as $um) {
    $umLower = strtolower($um);
    if (!in_array($umLower, $existentesMap)) {
        $stmt = $db->prepare("INSERT INTO unidademedida (unidademedida) VALUES (?)");
        $stmt->execute([$um]);
        echo "  - Inserida: $um\n";
    } else {
        echo "  - Já existe: $um\n";
    }
}

// Buscar IDs das unidades
$unidadesIds = [];
$stmt = $db->query("SELECT id, LOWER(unidademedida) as um FROM unidademedida");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $unidadesIds[$row['um']] = $row['id'];
}

// Inserir itens
echo "\nInserindo itens na tabela planilhas...\n";
$stmtInsert = $db->prepare("
    INSERT INTO planilhas (item, descricao, id_unidademedida, valor) 
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        descricao = VALUES(descricao),
        id_unidademedida = VALUES(id_unidademedida),
        valor = VALUES(valor)
");

$insertCount = 0;
foreach ($itens as $item) {
    $umId = $unidadesIds[strtolower($item['unidade'])] ?? null;
    
    $stmtInsert->execute([
        $item['item'],
        $item['descricao'],
        $umId,
        $item['valor']
    ]);
    $insertCount++;
    echo "  - $item[item]\n";
}

echo "\n=== Migração concluída ===\n";
echo "Itens inseridos: $insertCount\n";