<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGenerator;

/**
 * Usa SvgWriter (não PngWriter/GD) — o PngWriter deste ambiente (PHP 8.4 +
 * GD) trava com segmentation fault de forma intermitente ao gerar PNG,
 * confirmado em testes repetidos (com e sem Builder). SVG é vetorial, não
 * usa GD, e escala melhor para impressão em tamanho físico (cm).
 *
 * O barcode linear (Code128) usa o mesmo BarcodeGeneratorSVG (também livre
 * de GD, mesmo motivo) para dar suporte a locais que só têm leitor comum
 * de código de barras, sem leitor de QR.
 */

/** @var string[] $codigos */
$codigos = $codigos ?? [];

$qrWriter = new SvgWriter();
$barcodeGenerator = new BarcodeGeneratorSVG();

$qrItems = array_map(function (string $codigo) use ($qrWriter, $barcodeGenerator) {
    $qrCode = new QrCode(data: $codigo, size: 300, margin: 8);
    $qrResult = $qrWriter->write($qrCode);

    $barcodeSvg = $barcodeGenerator->getBarcode($codigo, BarcodeGenerator::TYPE_CODE_128, 2, 60);

    return [
        'codigo' => $codigo,
        'qrDataUri' => $qrResult->getDataUri(),
        'barcodeDataUri' => 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg),
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
    grid-template-columns: repeat(auto-fill, minmax(6.8cm, 1fr));
    gap: 0.4cm;
    background: #fff;
    padding: 0.4cm;
    border-radius: 8px;
  }

  .qr-card {
    width: 6.6cm;
    height: 3.7cm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    border: 1px solid #e2e8f0;
    padding: 0.1cm;
  }
  .qr-card .codes-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3cm;
  }
  .qr-card .qr-img {
    width: 3cm;
    height: 3cm;
    display: block;
    flex: none;
  }
  .qr-card .barcode-img {
    width: 3.2cm;
    height: 1.4cm;
    display: block;
    flex: none;
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
        <div class="codes-row">
          <img class="qr-img" src="<?= $item['qrDataUri'] ?>" alt="QR <?= htmlspecialchars($item['codigo']) ?>">
          <img class="barcode-img" src="<?= $item['barcodeDataUri'] ?>" alt="Código de barras <?= htmlspecialchars($item['codigo']) ?>">
        </div>
        <div class="codigo"><?= htmlspecialchars($item['codigo']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
