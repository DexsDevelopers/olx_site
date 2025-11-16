<?php
/**
 * Teste Simples - Verificar se PHP está funcionando
 */
echo "PHP está funcionando!<br>";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "<br>";
echo "Diretório: " . __DIR__ . "<br>";

if (file_exists(__DIR__ . '/config.php')) {
    echo "✓ config.php encontrado<br>";
    
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/includes/produtos.php';
    
    try {
        $produtos = new Produtos();
        $lista = $produtos->listar(true);
        echo "✓ Conexão com banco OK<br>";
        echo "✓ Produtos ativos: " . count($lista) . "<br>";
        
        if (!empty($lista)) {
            echo "<h3>Produtos encontrados:</h3>";
            echo "<ul>";
            foreach ($lista as $p) {
                echo "<li><strong>" . htmlspecialchars($p['titulo']) . "</strong> - R$ " . number_format($p['preco'], 2, ',', '.') . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>⚠ Nenhum produto ativo encontrado!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "✗ config.php NÃO encontrado<br>";
}

