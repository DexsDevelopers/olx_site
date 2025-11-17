<?php
/**
 * Página Inicial - Versão PHP Dinâmica
 * Carrega produtos do banco de dados
 */

// Headers para evitar cache
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

// Debug: verificar se há produtos
if (empty($listaProdutos)) {
    error_log("AVISO: Nenhum produto ativo encontrado no banco de dados!");
}

// Carregar o HTML original
$htmlFile = __DIR__ . '/index-inicio.html';
if (!file_exists($htmlFile)) {
    die("Erro: Arquivo index-inicio.html não encontrado!");
}
$htmlContent = file_get_contents($htmlFile);

// Gerar a seção de produtos dinâmica
$produtosHTML = renderProdutosCards($listaProdutos);

// Verificar se há produtos para exibir
if (!empty($listaProdutos)) {
    // Usar o padrão alternativo que é mais preciso (encontrou 8025 caracteres vs 185347)
    // Este padrão captura apenas a section específica, não tudo desde o comentário
    $pattern = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    
    // Substituir diretamente
    $htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);
    
    // Verificar se a substituição funcionou
    if (strpos($htmlContent, $produtosHTML) === false) {
        // Se não funcionou, tentar método alternativo mais específico
        $sectionStart = strpos($htmlContent, '<section');
        $sectionIdPos = strpos($htmlContent, 'id="produtos-lucas-template"');
        
        if ($sectionIdPos !== false) {
            // Encontrar o início da section (pode estar antes do ID)
            $sectionTagStart = strrpos(substr($htmlContent, 0, $sectionIdPos), '<section');
            if ($sectionTagStart !== false) {
                // Encontrar o fechamento correto
                $searchPos = $sectionTagStart;
                $depth = 0;
                $sectionEnd = false;
                
                while ($searchPos < strlen($htmlContent)) {
                    $nextOpen = strpos($htmlContent, '<section', $searchPos + 1);
                    $nextClose = strpos($htmlContent, '</section>', $searchPos);
                    
                    if ($nextClose === false) break;
                    
                    if ($nextOpen !== false && $nextOpen < $nextClose) {
                        $depth++;
                        $searchPos = $nextOpen;
                    } else {
                        if ($depth === 0) {
                            $sectionEnd = $nextClose + strlen('</section>');
                            break;
                        }
                        $depth--;
                        $searchPos = $nextClose;
                    }
                }
                
                if ($sectionEnd !== false) {
                    $htmlContent = substr_replace($htmlContent, $produtosHTML, $sectionTagStart, $sectionEnd - $sectionTagStart);
                }
            }
        }
    }
}

// Adicionar scripts antes do fechamento do body
$scripts = '
<script>
// Garantir que os produtos apareçam no mobile
(function() {
    function garantirExibicaoProdutos() {
        var template = document.getElementById("produtos-lucas-template");
        if (template) {
            // Forçar exibição
            template.style.display = "block";
            template.style.visibility = "visible";
            template.style.opacity = "1";
            
            // Garantir responsividade no mobile
            var grid = template.querySelector("div[style*=\"grid-template-columns\"]");
            if (grid) {
                // Ajustar para mobile (telas menores que 768px)
                if (window.innerWidth < 768) {
                    grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(140px, 1fr))";
                    grid.style.gap = "10px";
                } else {
                    grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(160px, 1fr))";
                    grid.style.gap = "12px";
                }
            }
        }
    }
    
    // Executar imediatamente
    garantirExibicaoProdutos();
    
    // Executar quando DOM estiver pronto
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", garantirExibicaoProdutos);
    }
    
    // Executar após um pequeno delay (fallback)
    setTimeout(garantirExibicaoProdutos, 100);
    
    // Executar no resize para ajustar responsividade
    window.addEventListener("resize", function() {
        var template = document.getElementById("produtos-lucas-template");
        if (template) {
            var grid = template.querySelector("div[style*=\"grid-template-columns\"]");
            if (grid && window.innerWidth < 768) {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(140px, 1fr))";
            } else if (grid) {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(160px, 1fr))";
            }
        }
    });
})();
</script>

<script>
// Script de atalho para painel admin
(function() {
    var keySequence = [];
    var targetSequence = "admin"; // Sequência de teclas para acessar admin
    var maxTime = 3000; // Tempo máximo entre teclas (3 segundos)
    var lastKeyTime = 0;
    
    document.addEventListener("keydown", function(e) {
        var currentTime = Date.now();
        
        // Reset se passou muito tempo desde a última tecla
        if (currentTime - lastKeyTime > maxTime) {
            keySequence = [];
        }
        
        lastKeyTime = currentTime;
        
        // Adicionar a tecla pressionada (apenas letras)
        var key = e.key.toLowerCase();
        if (key.length === 1 && /[a-z]/.test(key)) {
            keySequence.push(key);
            
            // Manter apenas os últimos caracteres (tamanho da sequência alvo)
            if (keySequence.length > targetSequence.length) {
                keySequence.shift();
            }
            
            // Verificar se a sequência corresponde
            if (keySequence.join("") === targetSequence) {
                // Redirecionar para o painel admin
                window.location.href = "admin/index.php";
            }
        }
    });
})();
</script>
';

// Inserir os scripts antes do fechamento do </body>
$htmlContent = preg_replace('/<\/body>/i', $scripts . '</body>', $htmlContent, 1);

// Output do HTML final
echo $htmlContent;

