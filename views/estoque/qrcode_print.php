<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * Usa SvgWriter (não PngWriter/GD) — o PngWriter deste ambiente (PHP 8.4 +
 * GD) trava com segmentation fault de forma intermitente ao gerar PNG,
 * confirmado em testes repetidos (com e sem Builder). SVG é vetorial, não
 * usa GD, e escala melhor para impressão em tamanho físico (cm).
 */

/** @var string[] $codigos */
$codigos = $codigos ?? [];

$writer = new SvgWriter();

$qrItems = array_map(function (string $codigo) use ($writer) {
    $qrCode = new QrCode(data: $codigo, size: 300, margin: 8);
    $result = $writer->write($qrCode);
    return [
        'codigo' => $codigo,
        'dataUri' => $result->getDataUri(),
    ];
}, $codigos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Imprimir QR Code') ?></title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f1f5f9;
    margin: 0;
    padding: 24px;
  }
  .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 900px;
    margin: 0 auto 20px;
  }
  .toolbar h1 { font-size: 18px; margin: 0; color: #0c1a2e; }
  .btn-print {
    background: #0ea5e9;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
  }
  .btn-print:hover { background: #0284c7; }

  .grid {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(3.4cm, 1fr));
    gap: 0.4cm;
    background: #fff;
    padding: 0.4cm;
    border-radius: 8px;
  }

  .qr-card {
    width: 3cm;
    height: 3.7cm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    border: 1px solid #e2e8f0;
    padding: 0.1cm;
  }
  .qr-card img {
    width: 3cm;
    height: 3cm;
    display: block;
  }
  .qr-card .codigo {
    font-size: 11px;
    font-weight: 700;
    text-align: center;
    margin-top: 2px;
    font-family: 'Courier New', monospace;
    word-break: break-all;
    line-height: 1.1;
  }

  @media print {
    body { background: #fff; padding: 0; }
    .toolbar { display: none; }
    .grid {
      padding: 0;
      border-radius: 0;
      gap: 0.3cm;
    }
    .qr-card {
      border: none;
      page-break-inside: avoid;
    }
    @page { margin: 1cm; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <h1><?= htmlspecialchars($title ?? 'Imprimir QR Code') ?> (<?= count($qrItems) ?>)</h1>
    <button class="btn-print" onclick="window.print()">Imprimir</button>
  </div>

  <div class="grid">
    <?php foreach ($qrItems as $item): ?>
      <div class="qr-card">
        <img src="<?= $item['dataUri'] ?>" alt="QR <?= htmlspecialchars($item['codigo']) ?>">
        <div class="codigo"><?= htmlspecialchars($item['codigo']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
