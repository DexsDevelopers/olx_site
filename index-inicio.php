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

// NOVA ABORDAGEM: Inserir produtos via JavaScript diretamente no DOM
// Isso evita que sejam removidos pelo JavaScript da OLX

// Preparar dados dos produtos para JavaScript
$produtosJSON = [];
foreach ($listaProdutos as $produto) {
    if ($produto['ativo'] == 1) {
        $produtosJSON[] = [
            'id' => $produto['id'],
            'titulo' => $produto['titulo'],
            'preco' => $produto['preco'],
            'imagem' => $produto['imagem_principal'],
            'localizacao' => $produto['localizacao'],
            'data' => $produto['data_publicacao'],
            'hora' => $produto['hora_publicacao'],
            'garantia' => $produto['garantia_olx'] == 1,
            'link' => $produto['link_pagina'] ?: 'index.html'
        ];
    }
}

// REMOVER o script original que tenta clonar (causa conflito)
$htmlContent = preg_replace(
    '/<script>\s*\/\/\s*Insere os produtos logo abaixo do carrossel.*?<\/script>/s',
    '<!-- Script original removido para evitar conflito -->',
    $htmlContent
);

// REMOVER também a section template original (não vamos mais usar)
$htmlContent = preg_replace(
    '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s',
    '<!-- Template original removido - produtos serão inseridos via JavaScript -->',
    $htmlContent
);

// Script que insere produtos dinamicamente (no head)
$produtosJS = json_encode($produtosJSON, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$scriptHead = '
<script>
// DADOS DOS PRODUTOS - Inserir dinamicamente no DOM
var PRODUTOS_DATA = ' . $produtosJS . ';

// Função para formatar preço
function formatarPreco(preco) {
    return "R$ " + parseFloat(preco).toLocaleString("pt-BR", {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Função para formatar data
function formatarData(data, hora) {
    if (!data) return "";
    var partes = data.split("-");
    if (partes.length === 3) {
        return partes[2] + "/" + partes[1] + "/" + partes[0] + (hora ? ", " + hora : "");
    }
    return data + (hora ? ", " + hora : "");
}

// Função para criar e inserir produtos
function inserirProdutos() {
    if (!PRODUTOS_DATA || PRODUTOS_DATA.length === 0) return;
    
    // Verificar se já existe (evitar duplicação)
    var existente = document.getElementById("produtos-bianca-dynamic");
    if (existente) {
        existente.style.display = "block";
        existente.style.visibility = "visible";
        existente.style.opacity = "1";
        return;
    }
    
    // Criar container
    var container = document.createElement("section");
    container.id = "produtos-bianca-dynamic";
    container.className = "Container_home-container__aomo5";
    container.style.cssText = "max-width: 1200px; margin: 24px auto 32px; display: block !important; visibility: visible !important; opacity: 1 !important; background: #111827; border-radius: 12px; padding: 16px; width: 100%; box-sizing: border-box; position: relative; z-index: 99999;";
    
    // Header
    var header = document.createElement("header");
    header.style.cssText = "display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 16px;";
    var span = document.createElement("span");
    span.style.cssText = "font-size: 12px; padding: 4px 10px; border-radius: 999px; border: 1px solid #4b5563; color: #e5e7eb; background: #111827;";
    span.textContent = "Produtos da Bianca Moraes";
    header.appendChild(document.createElement("div"));
    header.appendChild(span);
    
    // Grid de produtos
    var grid = document.createElement("div");
    var gridCols = window.innerWidth < 768 ? "repeat(auto-fit, minmax(140px, 1fr))" : "repeat(auto-fit, minmax(160px, 1fr))";
    grid.style.cssText = "display: grid; grid-template-columns: " + gridCols + "; gap: 12px; width: 100%;";
    
    // Criar cards
    PRODUTOS_DATA.forEach(function(produto) {
        var article = document.createElement("article");
        article.style.cssText = "background: #020617; border-radius: 10px; overflow: hidden; border: 1px solid #374151; display: flex; flex-direction: column; box-shadow: 0 6px 18px rgba(0,0,0,0.35); width: 100%; min-width: 0;";
        
        var link = document.createElement("a");
        link.href = "produto.php?p=" + encodeURIComponent(produto.link);
        link.style.cssText = "text-decoration: none; color: inherit; display: block; width: 100%;";
        
        // Imagem
        var imgDiv = document.createElement("div");
        imgDiv.style.cssText = "height: 150px; min-height: 120px; background: #020617; display: flex; align-items: center; justify-content: center; overflow: hidden; width: 100%;";
        var img = document.createElement("img");
        img.src = produto.imagem;
        img.alt = produto.titulo;
        img.style.cssText = "width: 100%; height: 100%; object-fit: cover; display: block;";
        imgDiv.appendChild(img);
        
        // Conteúdo
        var contentDiv = document.createElement("div");
        contentDiv.style.cssText = "padding: 10px 12px 12px; display: flex; flex-direction: column; gap: 4px; width: 100%; box-sizing: border-box;";
        
        var titulo = document.createElement("p");
        titulo.style.cssText = "font-size: 14px; font-weight: 600; color: #ffffff !important; margin: 0 0 6px; line-height: 1.3; word-wrap: break-word; overflow-wrap: break-word;";
        titulo.textContent = produto.titulo;
        contentDiv.appendChild(titulo);
        
        if (produto.garantia) {
            var garantiaDiv = document.createElement("div");
            garantiaDiv.style.cssText = "display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 6px;";
            var garantiaSpan = document.createElement("span");
            garantiaSpan.style.cssText = "display: inline-block; background-color: #f4edfc; color: #9c59d1; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px;";
            garantiaSpan.textContent = "Garantia da OLX";
            garantiaDiv.appendChild(garantiaSpan);
            contentDiv.appendChild(garantiaDiv);
        }
        
        var preco = document.createElement("p");
        preco.style.cssText = "font-weight: bold; font-size: 18px; margin: 0 0 4px; color: #ffffff !important; line-height: 1.2;";
        preco.textContent = formatarPreco(produto.preco);
        contentDiv.appendChild(preco);
        
        var info = document.createElement("p");
        info.style.cssText = "font-size: 12px; color: #9ca3af !important; margin: 0; line-height: 1.3;";
        info.textContent = produto.localizacao + " • " + formatarData(produto.data, produto.hora);
        contentDiv.appendChild(info);
        
        link.appendChild(imgDiv);
        link.appendChild(contentDiv);
        article.appendChild(link);
        grid.appendChild(article);
    });
    
    var contentDiv = document.createElement("div");
    contentDiv.className = "Container_home-container__content__4lhbl";
    contentDiv.appendChild(header);
    contentDiv.appendChild(grid);
    container.appendChild(contentDiv);
    
    // Inserir antes do </body>
    if (document.body) {
        document.body.appendChild(container);
    } else {
        document.addEventListener("DOMContentLoaded", function() {
            document.body.appendChild(container);
        });
    }
    
    // Proteger contra remoção
    Object.defineProperty(container, "remove", {
        value: function() { 
            setTimeout(inserirProdutos, 0);
            return; 
        },
        writable: false,
        configurable: false
    });
}

// Executar inserção
inserirProdutos();
setTimeout(inserirProdutos, 0);
setTimeout(inserirProdutos, 100);
setTimeout(inserirProdutos, 500);

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", inserirProdutos);
}

window.addEventListener("load", inserirProdutos);

// Verificar e restaurar se for removido
setInterval(function() {
    var el = document.getElementById("produtos-bianca-dynamic");
    if (!el || !document.body.contains(el)) {
        inserirProdutos();
    } else {
        var computed = window.getComputedStyle(el);
        if (computed.display === "none" || computed.visibility === "hidden") {
            el.style.display = "block";
            el.style.visibility = "visible";
            el.style.opacity = "1";
        }
    }
}, 500);
</script>
';

// Adicionar scripts antes do fechamento do body
$scripts = '
<script>
// Proteção adicional para produtos inseridos dinamicamente
(function() {
    var originalRemoveChild = Node.prototype.removeChild;
    Node.prototype.removeChild = function(child) {
        if (child && (child.id === "produtos-bianca-dynamic" || child.id === "produtos-lucas-template")) {
            console.warn("Tentativa de remover produtos bloqueada!");
            setTimeout(function() {
                if (typeof inserirProdutos === "function") {
                    inserirProdutos();
                }
            }, 0);
            return child;
        }
        return originalRemoveChild.call(this, child);
    };
    
    if (Element.prototype.remove) {
        var originalRemove = Element.prototype.remove;
        Element.prototype.remove = function() {
            if (this.id === "produtos-bianca-dynamic" || this.id === "produtos-lucas-template") {
                console.warn("Tentativa de remover produtos bloqueada!");
                setTimeout(function() {
                    if (typeof inserirProdutos === "function") {
                        inserirProdutos();
                    }
                }, 0);
                return;
            }
            return originalRemove.call(this);
        };
    }
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
    
    // Executar imediatamente (antes de qualquer coisa)
    (function() {
        garantirExibicaoProdutos();
    })();
    
    // Executar quando DOM estiver pronto
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function() {
            garantirExibicaoProdutos();
            verificarContinuamente();
        });
    } else {
        verificarContinuamente();
    }
    
    // Executar após delays para garantir (múltiplos pontos)
    setTimeout(garantirExibicaoProdutos, 10);
    setTimeout(garantirExibicaoProdutos, 50);
    setTimeout(garantirExibicaoProdutos, 100);
    setTimeout(garantirExibicaoProdutos, 200);
    setTimeout(garantirExibicaoProdutos, 500);
    setTimeout(garantirExibicaoProdutos, 1000);
    setTimeout(garantirExibicaoProdutos, 2000);
    
    // Executar após window load (último recurso)
    window.addEventListener("load", function() {
        garantirExibicaoProdutos();
        setTimeout(garantirExibicaoProdutos, 100);
        setTimeout(garantirExibicaoProdutos, 500);
    });
    
    // Executar quando página fica visível (mobile específico)
    if (document.addEventListener) {
        document.addEventListener("visibilitychange", function() {
            if (!document.hidden) {
                garantirExibicaoProdutos();
            }
        });
    }
    
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
                // Não permitir esconder e forçar exibição
                setTimeout(function() {
                    garantirExibicaoProdutos();
                }, 0);
                return;
            }
        }
        return originalSetProperty.call(this, property, value, priority);
    };
    
    // Interceptar também style.display direto
    var templateElement = null;
    function interceptStyle() {
        templateElement = document.getElementById("produtos-lucas-template");
        if (templateElement) {
            Object.defineProperty(templateElement.style, "display", {
                set: function(value) {
                    if (value === "none") {
                        setTimeout(function() {
                            templateElement.style.setProperty("display", "block", "important");
                        }, 0);
                    } else {
                        Object.getOwnPropertyDescriptor(CSSStyleDeclaration.prototype, "display").set.call(this, value);
                    }
                },
                get: function() {
                    return "block";
                },
                configurable: true
            });
        }
    }
    
    // Tentar interceptar quando elemento estiver disponível
    setTimeout(interceptStyle, 0);
    setTimeout(interceptStyle, 100);
    setTimeout(interceptStyle, 500);
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", interceptStyle);
    }
})();
</script>

<script>
// FALLBACK ULTRA SIMPLES - Executa no final de tudo
(function() {
    function forcarExibicao() {
        var el = document.getElementById("produtos-lucas-template");
        if (el) {
            el.style.cssText = "display: block !important; visibility: visible !important; opacity: 1 !important; height: auto !important; overflow: visible !important; position: relative !important; z-index: 9999 !important; max-width: 1200px; margin: 24px auto 32px; background: #111827; border-radius: 12px; padding: 16px; width: 100%; box-sizing: border-box;";
        }
    }
    
    // Executar várias vezes
    forcarExibicao();
    setTimeout(forcarExibicao, 0);
    setTimeout(forcarExibicao, 50);
    setTimeout(forcarExibicao, 100);
    setTimeout(forcarExibicao, 200);
    setTimeout(forcarExibicao, 500);
    setTimeout(forcarExibicao, 1000);
    
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", forcarExibicao);
    }
    
    window.addEventListener("load", forcarExibicao);
    
    // Verificar a cada segundo por 10 segundos
    var count = 0;
    var interval = setInterval(function() {
        count++;
        forcarExibicao();
        if (count >= 10) {
            clearInterval(interval);
        }
    }, 1000);
    
    // Observer ULTRA AGRESSIVO - verifica a cada 100ms
    var observerInterval = setInterval(function() {
        var el = document.getElementById("produtos-lucas-template");
        if (el) {
            var computed = window.getComputedStyle(el);
            if (computed.display === "none" || computed.visibility === "hidden" || computed.opacity === "0") {
                forcarExibicao();
            }
        }
    }, 100);
    
    // Parar após 30 segundos (economia de recursos)
    setTimeout(function() {
        clearInterval(observerInterval);
    }, 30000);
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

// Adicionar meta tags anti-cache e CSS inline
$metaCache = '
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
';

$cssFix = '
<style id="produtos-fix-css">
/* Forçar exibição em TODOS os casos */
#produtos-lucas-template,
section#produtos-lucas-template,
[id="produtos-lucas-template"],
.Container_home-container__aomo5#produtos-lucas-template {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    height: auto !important;
    overflow: visible !important;
    position: relative !important;
    z-index: 9999 !important;
}

#produtos-lucas-template *,
#produtos-lucas-template > *,
#produtos-lucas-template div,
#produtos-lucas-template article {
    visibility: visible !important;
    display: block !important;
}

#produtos-lucas-template article {
    display: flex !important;
}

/* Mobile específico */
@media (max-width: 768px) {
    #produtos-lucas-template {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        padding: 12px !important;
        margin: 16px auto 24px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    #produtos-lucas-template div[style*="grid-template-columns"],
    #produtos-lucas-template > div > div {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
        gap: 10px !important;
        width: 100% !important;
    }
    
    #produtos-lucas-template article {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Fallback para qualquer dispositivo móvel */
@media (pointer: coarse) {
    #produtos-lucas-template {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Touch devices */
@media (hover: none) {
    #produtos-lucas-template {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}
</style>
';

// Inserir meta tags, CSS e script de proteção no head, e scripts antes do fechamento do </body>
// Primeiro, adicionar meta tags e script de proteção no head (após charset/viewport se existir)
if (preg_match('/<head[^>]*>/i', $htmlContent)) {
    $htmlContent = preg_replace('/(<head[^>]*>)/i', '$1' . $metaCache . $scriptHead, $htmlContent, 1);
} else {
    // Se não tiver head, criar um
    $htmlContent = preg_replace('/<html[^>]*>/i', '<html>' . "\n<head>" . $metaCache . $scriptHead . '</head>', $htmlContent, 1);
}

$htmlContent = preg_replace('/<\/head>/i', $cssFix . '</head>', $htmlContent, 1);
$htmlContent = preg_replace('/<\/body>/i', $scripts . '</body>', $htmlContent, 1);

// Output do HTML final
echo $htmlContent;

