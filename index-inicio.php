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
// DESABILITAR o script original que tenta clonar (pode estar causando conflito)
(function() {
    // Sobrescrever o event listener original se existir
    var originalAddEventListener = document.addEventListener;
    document.addEventListener = function(type, listener, options) {
        if (type === "DOMContentLoaded" && listener.toString().indexOf("produtos-lucas-template") !== -1 && listener.toString().indexOf("cloneNode") !== -1) {
            // Não executar o listener original que clona
            return;
        }
        return originalAddEventListener.call(this, type, listener, options);
    };
})();
</script>

<script>
// Garantir que os produtos apareçam no mobile - VERSÃO ROBUSTA
(function() {
    var tentativas = 0;
    var maxTentativas = 50; // Verificar por até 5 segundos (50 x 100ms)
    
    function garantirExibicaoProdutos() {
        var template = document.getElementById("produtos-lucas-template");
        if (template) {
            // Forçar exibição com múltiplas propriedades
            template.style.setProperty("display", "block", "important");
            template.style.setProperty("visibility", "visible", "important");
            template.style.setProperty("opacity", "1", "important");
            template.style.setProperty("height", "auto", "important");
            template.style.setProperty("overflow", "visible", "important");
            
            // Remover qualquer classe que possa estar escondendo
            template.classList.remove("d-none", "hidden", "invisible");
            
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
            
            // Verificar se ainda está escondido e forçar novamente
            var computedStyle = window.getComputedStyle(template);
            if (computedStyle.display === "none" || computedStyle.visibility === "hidden" || computedStyle.opacity === "0") {
                template.style.setProperty("display", "block", "important");
                template.style.setProperty("visibility", "visible", "important");
                template.style.setProperty("opacity", "1", "important");
            }
            
            return true; // Sucesso
        }
        return false; // Template não encontrado ainda
    }
    
    // Função para verificar continuamente
    function verificarContinuamente() {
        if (tentativas < maxTentativas) {
            tentativas++;
            if (!garantirExibicaoProdutos()) {
                // Se não encontrou, tentar novamente
                setTimeout(verificarContinuamente, 100);
            } else {
                // Encontrou e configurou, mas continuar verificando para garantir
                setTimeout(function() {
                    garantirExibicaoProdutos();
                }, 500);
            }
        }
    }
    
    // Executar imediatamente
    garantirExibicaoProdutos();
    
    // Executar quando DOM estiver pronto
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function() {
            garantirExibicaoProdutos();
            verificarContinuamente();
        });
    } else {
        verificarContinuamente();
    }
    
    // Executar após delays para garantir
    setTimeout(garantirExibicaoProdutos, 50);
    setTimeout(garantirExibicaoProdutos, 200);
    setTimeout(garantirExibicaoProdutos, 500);
    setTimeout(garantirExibicaoProdutos, 1000);
    
    // Observer para detectar mudanças no DOM e garantir exibição
    if (window.MutationObserver) {
        var observer = new MutationObserver(function(mutations) {
            var template = document.getElementById("produtos-lucas-template");
            if (template) {
                var computedStyle = window.getComputedStyle(template);
                if (computedStyle.display === "none" || computedStyle.visibility === "hidden") {
                    garantirExibicaoProdutos();
                }
            }
        });
        
        // Observar mudanças no body
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ["style", "class"]
            });
        } else {
            document.addEventListener("DOMContentLoaded", function() {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ["style", "class"]
                });
            });
        }
    }
    
    // Executar no resize para ajustar responsividade
    window.addEventListener("resize", function() {
        var template = document.getElementById("produtos-lucas-template");
        if (template) {
            garantirExibicaoProdutos();
            var grid = template.querySelector("div[style*=\"grid-template-columns\"]");
            if (grid && window.innerWidth < 768) {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(140px, 1fr))";
            } else if (grid) {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(160px, 1fr))";
            }
        }
    });
    
    // Interceptar tentativas de esconder o elemento
    var originalSetProperty = CSSStyleDeclaration.prototype.setProperty;
    CSSStyleDeclaration.prototype.setProperty = function(property, value, priority) {
        var element = this;
        if (element && element.ownerElement && element.ownerElement.id === "produtos-lucas-template") {
            if ((property === "display" && value === "none") || 
                (property === "visibility" && value === "hidden") ||
                (property === "opacity" && value === "0")) {
                // Não permitir esconder
                return;
            }
        }
        return originalSetProperty.call(this, property, value, priority);
    };
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

// Adicionar CSS inline para garantir que produtos não sejam escondidos
$cssFix = '
<style id="produtos-fix-css">
#produtos-lucas-template {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    height: auto !important;
    overflow: visible !important;
}

#produtos-lucas-template * {
    visibility: visible !important;
}

@media (max-width: 768px) {
    #produtos-lucas-template {
        padding: 12px !important;
        margin: 16px auto 24px !important;
    }
    
    #produtos-lucas-template div[style*="grid-template-columns"] {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
        gap: 10px !important;
    }
}
</style>
';

// Inserir CSS no head e scripts antes do fechamento do </body>
$htmlContent = preg_replace('/<\/head>/i', $cssFix . '</head>', $htmlContent, 1);
$htmlContent = preg_replace('/<\/body>/i', $scripts . '</body>', $htmlContent, 1);

// Output do HTML final
echo $htmlContent;

