<?php
/**
 * Template para renderizar cards de produtos
 */
function renderProdutosCards($produtos) {
    require_once __DIR__ . '/produtos.php';
    $produtosObj = new Produtos();
    ob_start();
    ?>
    <section id="produtos-lucas-template" class="Container_home-container__aomo5" style="max-width: 1200px; margin: 24px auto 32px; display: block !important; visibility: visible !important; opacity: 1 !important; background:#111827; border-radius:12px; padding:16px 16px 20px; width: 100%; box-sizing: border-box; position: relative; z-index: 9999;">
      <div class="Container_home-container__content__4lhbl">
        <header style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 16px;">
          <h2 style="margin:0; font-size:18px; color:#f3f4f6; font-weight:700;">Produtos da Bianca Moraes</h2>
          <span style="font-size: 12px; padding: 4px 10px; border-radius: 999px; border: 1px solid #4b5563; color: #e5e7eb; background:#111827;">
            Estoque selecionado e atualizado
          </span>
        </header>

        <div class="produtos-carousel-wrapper">
          <button type="button" class="produtos-carousel-btn produtos-carousel-btn--prev" aria-label="Ver produtos anteriores">‹</button>
          <div class="produtos-carousel-viewport">
            <div class="produtos-carousel-track">
            <?php foreach ($produtos as $produto): ?>
              <?php if ($produto['ativo'] == 1): ?>
              <article style="flex:0 0 240px; max-width:240px; background: #020617; border-radius: 12px; overflow: hidden; border: 1px solid #1f2937; display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.40); scroll-snap-align:start;">
                <a href="produto.php?p=<?= htmlspecialchars(urlencode($produto['link_pagina'] ?: 'index.html')) ?>" style="text-decoration:none; color:inherit; display: flex; flex-direction: column; height:100%;">
                  <div style="height: 150px; min-height: 120px; background: #020617; display: flex; align-items: center; justify-content: center; overflow:hidden;">
                    <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['titulo']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                  </div>
                  <div style="padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 6px; flex:1;">
                    <p style="font-size: 15px; font-weight: 600; color:#ffffff !important; margin:0; line-height: 1.35; word-wrap: break-word;"><?= htmlspecialchars($produto['titulo']) ?></p>
                    <?php if ($produto['garantia_olx'] == 1): ?>
                    <span style="display:inline-flex; align-items:center; gap:4px; background-color:rgba(156,89,209,0.15); color:#c084fc; font-size:11px; font-weight:600; padding:3px 7px; border-radius:999px; width:max-content;">
                      <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M8 1l6 3v4c0 3.07-2.13 5.64-6 7-3.87-1.36-6-3.93-6-7V4l6-3z"/></svg>
                      Garantia OLX
                    </span>
                    <?php endif; ?>
                    <p style="font-weight:bold; font-size:19px; margin:0; color:#ffffff !important;"><?= $produtosObj->formatarPreco($produto['preco']) ?></p>
                    <p style="font-size: 12px; color: #9ca3af !important; margin-top:auto; line-height: 1.35;"><?= htmlspecialchars($produto['localizacao']) ?> • <?= $produtosObj->formatarData($produto['data_publicacao'], $produto['hora_publicacao']) ?></p>
                  </div>
                </a>
              </article>
              <?php endif; ?>
            <?php endforeach; ?>
            </div>
          </div>
          <button type="button" class="produtos-carousel-btn produtos-carousel-btn--next" aria-label="Ver próximos produtos">›</button>
        </div>
      </div>
    </section>
    <style id="produtos-carousel-style">
      #produtos-lucas-template .produtos-carousel-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
      }
      #produtos-lucas-template .produtos-carousel-viewport {
        flex: 1;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 8px 4px 12px;
        scrollbar-width: none;
        -ms-overflow-style: none;
      }
      #produtos-lucas-template .produtos-carousel-viewport::-webkit-scrollbar {
        display: none;
      }
      #produtos-lucas-template .produtos-carousel-track {
        display: flex;
        gap: 12px;
        min-width: fit-content;
      }
      #produtos-lucas-template .produtos-carousel-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: rgba(8, 15, 40, 0.65);
        color: #f9fafb;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
        flex: 0 0 auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
      }
      #produtos-lucas-template .produtos-carousel-btn:hover {
        background: rgba(8, 15, 40, 0.9);
      }
      @media (max-width: 768px) {
        #produtos-lucas-template .produtos-carousel-wrapper {
          gap: 8px;
        }
        #produtos-lucas-template .produtos-carousel-btn {
          width: 30px;
          height: 30px;
          font-size: 16px;
        }
      }
    </style>
    <script>
      (function () {
        var section = document.getElementById('produtos-lucas-template');
        if (!section) return;
        var viewport = section.querySelector('.produtos-carousel-viewport');
        var track = section.querySelector('.produtos-carousel-track');
        var scrollContainer = viewport || track;
        if (!scrollContainer) return;
        var prevBtn = section.querySelector('.produtos-carousel-btn--prev');
        var nextBtn = section.querySelector('.produtos-carousel-btn--next');

        function getScrollAmount() {
          var card = section.querySelector('.produtos-carousel-track article');
          if (!card) return 260;
          var styles = window.getComputedStyle(scrollContainer);
          var gap = parseInt(styles.columnGap || styles.gap || '12', 10) || 12;
          return card.getBoundingClientRect().width + gap;
        }

        function scroll(delta) {
          scrollContainer.scrollBy({ left: delta, behavior: 'smooth' });
        }

        if (prevBtn) {
          prevBtn.addEventListener('click', function () {
            scroll(-getScrollAmount());
          });
        }
        if (nextBtn) {
          nextBtn.addEventListener('click', function () {
            scroll(getScrollAmount());
          });
        }
      })();
    </script>
    <?php
    return ob_get_clean();
}

