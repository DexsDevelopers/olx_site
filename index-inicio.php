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

$produtosHTML = renderProdutosCards($listaProdutos);

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

// CTA flutuante no mobile para rolar até os produtos
$ctaMobile = <<<HTML
<style>
  @media (max-width: 768px) {
    #bianca-cta-mobile {
      position: fixed;
      bottom: 18px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 100000;
      background: linear-gradient(135deg, #4f46e5, #8b5cf6);
      color: #f9fafb;
      padding: 10px 16px;
      border-radius: 999px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.35);
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }
    #bianca-cta-mobile span {
      display: flex;
      flex-direction: column;
      line-height: 1.15;
    }
    #bianca-cta-mobile small {
      font-size: 11px;
      font-weight: 500;
      opacity: 0.9;
    }
    #bianca-cta-mobile-icon {
      width: 22px;
      height: 22px;
      border-radius: 999px;
      background: rgba(15,23,42,0.18);
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
</style>
<div id="bianca-cta-mobile">
  <div id="bianca-cta-mobile-icon">📦</div>
  <span>
    <strong>Ver produtos da Bianca</strong>
    <small>ofertas conferidas, role até os anúncios</small>
  </span>
</div>
<script>
(function() {
  function scrollToProdutos() {
    var alvo = document.getElementById('produtos-lucas-template');
    if (alvo) {
      try {
        alvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) {
        var y = alvo.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo(0, y);
      }
    }
  }
  var cta = document.getElementById('bianca-cta-mobile');
  if (cta) {
    cta.addEventListener('click', scrollToProdutos);
  }
})();
</script>
HTML;

// Se houver marcador específico no HTML, substitui por nossos produtos (posição fixa no layout)
if (strpos($htmlContent, '<!-- PRODUTOS_BIANCA -->') !== false) {
    $htmlContent = str_replace('<!-- PRODUTOS_BIANCA -->', $produtosHTML . $ctaMobile . $atalhoAdmin, $htmlContent);
} else {
    // Fallback: insere antes do </body> se o marcador não existir
    $htmlContent = preg_replace('/<\/body>/i', $produtosHTML . $ctaMobile . $atalhoAdmin . '</body>', $htmlContent, 1);
}

// Output final
echo $htmlContent;


