<?php
// Teste super simples
echo "OK - PHP está funcionando!<br>";
echo "Data: " . date('Y-m-d H:i:s') . "<br>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Arquivo atual: " . __FILE__ . "<br>";

// Verificar se outros arquivos existem
$files = ['config.php', 'database.php', 'index-inicio.php', 'debug-produtos.php'];
echo "<h3>Verificando arquivos:</h3><ul>";
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<li>$file: " . ($exists ? "✓ Existe" : "✗ Não encontrado") . "</li>";
}
echo "</ul>";
?>

