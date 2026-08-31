<?php
require_once 'fpdf/fpdf.php';
require_once 'fpdf/makefont/makefont.php';

function converterFontes($diretorio) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($diretorio, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $total = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'ttf') {
            $caminho_ttf = $file->getPathname();
            // Gera os arquivos na própria pasta do TTF
            MakeFont($caminho_ttf, 'cp1252');
            echo "✔ Convertido: " . $file->getFilename() . "<br>";
            $total++;
        }
    }
    return $total;
}

echo "<h3>Iniciando conversão de fontes para FPDF...</h3>";
$qtd = converterFontes(__DIR__ . '/fontes');
echo "<br><b>Processo concluído! Total de fontes convertidas: $qtd</b>";
?>