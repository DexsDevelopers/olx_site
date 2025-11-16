<?php
/**
 * Template para renderizar cards de produtos
 */
function renderProdutosCards($produtos) {
    require_once __DIR__ . '/produtos.php';
    $produtosObj = new Produtos();
    ob_start();
    ?>
    <section id="produtos-lucas-template" class="Container_home-container__aomo5" style="max-width: 1200px; margin: 24px auto 32px; display:none; background:#111827; border-radius:12px; padding:16px 16px 20px;">
      <div class="Container_home-container__content__4lhbl">
        <header style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 16px;">
          <div></div>
          <span style="font-size: 12px; padding: 4px 10px; border-radius: 999px; border: 1px solid #4b5563; color: #e5e7eb; background:#111827;">
            Produtos da Bianca Moraes
          </span>
        </header>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
        <?php foreach ($produtos as $produto): ?>
          <?php if ($produto['ativo'] == 1): ?>
          <article style="background: #020617; border-radius: 10px; overflow: hidden; border: 1px solid #374151; display: flex; flex-direction: column; box-shadow: 0 6px 18px rgba(0,0,0,0.35);">
            <a href="produto.php?p=<?= htmlspecialchars(urlencode($produto['link_pagina'] ?: 'index.html')) ?>" style="text-decoration:none; color:inherit;">
              <div style="height: 150px; background: #020617; display: flex; align-items: center; justify-content: center; overflow:hidden;">
                <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['titulo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <div style="padding: 10px 12px 12px; display: flex; flex-direction: column; gap: 4px;">
                <p style="font-size: 14px; font-weight: 600; color:#ffffff !important; margin:0 0 6px;"><?= htmlspecialchars($produto['titulo']) ?></p>
                <?php if ($produto['garantia_olx'] == 1): ?>
                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:6px;">
                  <span style="display:inline-block; background-color:#f4edfc; color:#9c59d1; font-size:11px; font-weight:600; padding:2px 6px; border-radius:4px;">
                    Garantia da OLX
                  </span>
                </div>
                <?php endif; ?>
                <p style="font-weight:bold; font-size:18px; margin:0 0 4px; color:#ffffff !important;"><?= $produtosObj->formatarPreco($produto['preco']) ?></p>
                <p style="font-size: 12px; color: #9ca3af !important; margin:0;"><?= htmlspecialchars($produto['localizacao']) ?> • <?= $produtosObj->formatarData($produto['data_publicacao'], $produto['hora_publicacao']) ?></p>
              </div>
            </a>
          </article>
          <?php endif; ?>
        <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

