<?php

require __DIR__.'/../vendor/autoload.php';

$fontDir = __DIR__.'/../public/fonts';
$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'fontDir' => [$fontDir],
    'fontdata' => [
        'thsarabunnew' => [
            'R' => 'THSarabunNew.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'I' => 'THSarabunNew Italic.ttf',
            'BI' => 'THSarabunNew BoldItalic.ttf',
        ],
    ],
    'default_font' => 'thsarabunnew',
    'autoScriptToLang' => true,
    'autoLangToFont' => true,
    'tempDir' => sys_get_temp_dir(),
]);

$pdf->WriteHTML(file_get_contents(__DIR__.'/system-summary.html'));
$outputPath = __DIR__.'/URU-Coop-IS-system-summary.pdf';
$pdf->Output($outputPath, 'F');

$content = file_get_contents($outputPath);
if (substr($content, 0, 5) !== '%PDF-' || strpos(substr($content, -1024), '%%EOF') === false) {
    throw new RuntimeException('Generated PDF failed signature validation.');
}

echo $outputPath.PHP_EOL;