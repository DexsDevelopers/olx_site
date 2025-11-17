<?php
/**
 * Página Inicial - Versão PHP Dinâmica simples
 * Usa o template HTML original e substitui o bloco de produtos
 * pelo template PHP (`renderProdutosCards`), igual à versão que já funcionava antes.
 */

// Headers para evitar cache agressivo
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';
require_once __DIR__ . '/includes/produtos_template.php';

// Buscar produtos ativos do banco
$produtos = new Produtos();
$listaProdutos = $produtos->listar(true); // apenas ativos

// Carregar o HTML original da página inicial
$htmlFile = __DIR__ . '/index-inicio.html';
if (!file_exists($htmlFile)) {
    http_response_code(500);
    echo 'Erro: arquivo index-inicio.html não encontrado.';
    exit;
}

$htmlContent = file_get_contents($htmlFile);

// Gerar a seção de produtos dinâmica usando o template antigo (PHP)
$produtosHTML = renderProdutosCards($listaProdutos);

// Substituir o bloco personalizado de produtos original pelo HTML dinâmico
// Este padrão é o mesmo que foi usado quando "antes os produtos apareciam"
$pattern = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<\/section>/s';
$novoHtml = preg_replace($pattern, $produtosHTML, $htmlContent, 1);

if ($novoHtml === null) {
    // Em caso de erro na regex, logar e manter HTML original
    error_log('Erro em preg_replace ao substituir bloco de produtos em index-inicio.php: ' . preg_last_error());
} else {
    $htmlContent = $novoHtml;
}

// Adicionar script simples de atalho para admin (digitar "admin")
$atalhoAdmin = <<<HTML
<script>
// Atalho de teclado para abrir o painel admin digitando "admin"
(function() {
    var seq = [];
    var alvo = "admin";
    var maxTime = 3000; // 3 segundos entre teclas
    var last = 0;

    document.addEventListener("keydown", function(e) {
        var agora = Date.now();

        // Se demorou muito entre teclas, reinicia sequência
        if (agora - last > maxTime) {
            seq = [];
        }
        last = agora;

        var k = (e.key || "").toLowerCase();
        if (k.length === 1 && /[a-z]/.test(k)) {
            seq.push(k);
            if (seq.length > alvo.length) {
                seq.shift();
            }
            if (seq.join("") === alvo) {
                window.location.href = "admin/index.php";
            }
        }
    });
})();
</script>
HTML;

// Inserir o script de atalho antes do </body>
$htmlContent = preg_replace('/<\/body>/i', $atalhoAdmin . '</body>', $htmlContent, 1);

// Output final
echo $htmlContent;


