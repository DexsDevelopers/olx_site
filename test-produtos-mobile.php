<?php
/**
 * Página de teste isolada para verificar cards de produtos no mobile
 * Busca produtos do banco de dados
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

// Buscar produtos ativos
$produtos = new Produtos();
$listaProdutos = $produtos->listar(true); // apenas ativos
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Teste Produtos - Mobile (PHP)</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            padding: 16px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 24px;
            color: #ffffff;
            font-size: 24px;
        }
        
        .produtos-section {
            background: #111827;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            margin-bottom: 32px;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .produtos-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .produtos-badge {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #4b5563;
            color: #e5e7eb;
            background: #111827;
        }
        
        .produtos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .produtos-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
            }
        }
        
        .produto-card {
            background: #020617;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #374151;
            display: flex;
            flex-direction: column;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
            width: 100%;
            min-width: 0;
        }
        
        .produto-link {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
        }
        
        .produto-imagem-container {
            height: 150px;
            min-height: 120px;
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            width: 100%;
        }
        
        .produto-imagem {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .produto-conteudo {
            padding: 10px 12px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }
        
        .produto-titulo {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            margin: 0 0 6px;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .produto-garantia {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 6px;
        }
        
        .garantia-badge {
            display: inline-block;
            background-color: #f4edfc;
            color: #9c59d1;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .produto-preco {
            font-weight: bold;
            font-size: 18px;
            margin: 0 0 4px;
            color: #ffffff;
            line-height: 1.2;
        }
        
        .produto-info {
            font-size: 12px;
            color: #9ca3af;
            margin: 0;
            line-height: 1.3;
        }
        
        .debug-info {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
        
        .debug-info strong {
            color: #e2e8f0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste de Produtos - Mobile (PHP)</h1>
        
        <section class="produtos-section" id="produtos-test">
            <div class="produtos-header">
                <div></div>
                <span class="produtos-badge">Produtos da Bianca Moraes</span>
            </div>
            
            <div class="produtos-grid" id="produtos-grid">
                <?php if (empty($listaProdutos)): ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <p>Nenhum produto ativo encontrado no banco de dados.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($listaProdutos as $produto): ?>
                        <?php if ($produto['ativo'] == 1): ?>
                        <article class="produto-card">
                            <a href="produto.php?p=<?= htmlspecialchars(urlencode($produto['link_pagina'] ?: 'index.html')) ?>" class="produto-link">
                                <div class="produto-imagem-container">
                                    <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" 
                                         alt="<?= htmlspecialchars($produto['titulo']) ?>" 
                                         class="produto-imagem">
                                </div>
                                <div class="produto-conteudo">
                                    <p class="produto-titulo"><?= htmlspecialchars($produto['titulo']) ?></p>
                                    <?php if ($produto['garantia_olx'] == 1): ?>
                                    <div class="produto-garantia">
                                        <span class="garantia-badge">Garantia da OLX</span>
                                    </div>
                                    <?php endif; ?>
                                    <p class="produto-preco"><?= $produtos->formatarPreco($produto['preco']) ?></p>
                                    <p class="produto-info">
                                        <?= htmlspecialchars($produto['localizacao']) ?> • 
                                        <?= $produtos->formatarData($produto['data_publicacao'], $produto['hora_publicacao']) ?>
                                    </p>
                                </div>
                            </a>
                        </article>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        
        <div class="debug-info">
            <strong>Informações de Debug:</strong><br>
            Largura da tela: <span id="screen-width"></span>px<br>
            Altura da tela: <span id="screen-height"></span>px<br>
            User Agent: <span id="user-agent"></span><br>
            Total de produtos: <strong><?= count(array_filter($listaProdutos, function($p) { return $p['ativo'] == 1; })) ?></strong><br>
            Cards visíveis: <span id="cards-count">0</span>
        </div>
    </div>
    
    <script>
        // Debug info
        document.getElementById('screen-width').textContent = window.innerWidth;
        document.getElementById('screen-height').textContent = window.innerHeight;
        document.getElementById('user-agent').textContent = navigator.userAgent;
        
        // Ajustar grid no resize
        function ajustarGrid() {
            var grid = document.getElementById("produtos-grid");
            if (window.innerWidth < 768) {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(140px, 1fr))";
            } else {
                grid.style.gridTemplateColumns = "repeat(auto-fit, minmax(160px, 1fr))";
            }
            document.getElementById('screen-width').textContent = window.innerWidth;
            document.getElementById('screen-height').textContent = window.innerHeight;
        }
        
        window.addEventListener("resize", ajustarGrid);
        ajustarGrid();
        
        // Verificar se cards estão visíveis
        function verificarVisibilidade() {
            var cards = document.querySelectorAll('.produto-card');
            var visiveis = 0;
            cards.forEach(function(card) {
                var style = window.getComputedStyle(card);
                var rect = card.getBoundingClientRect();
                if (style.display !== 'none' && 
                    style.visibility !== 'hidden' && 
                    style.opacity !== '0' &&
                    rect.width > 0 &&
                    rect.height > 0) {
                    visiveis++;
                }
            });
            document.getElementById("cards-count").textContent = visiveis;
        }
        
        setInterval(verificarVisibilidade, 1000);
        verificarVisibilidade();
        
        // Verificar se a seção está visível
        function verificarSecao() {
            var secao = document.getElementById("produtos-test");
            if (secao) {
                var style = window.getComputedStyle(secao);
                console.log("Seção produtos:", {
                    display: style.display,
                    visibility: style.visibility,
                    opacity: style.opacity,
                    width: style.width,
                    height: style.height
                });
            }
        }
        
        setTimeout(verificarSecao, 100);
        setTimeout(verificarSecao, 500);
        setTimeout(verificarSecao, 1000);
    </script>
</body>
</html>

