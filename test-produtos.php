<?php
/**
 * Teste rápido - Verificar se produtos estão sendo carregados
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TESTE DE PRODUTOS ===\n\n";

try {
    $produtos = new Produtos();
    $listaProdutos = $produtos->listar(true);
    
    echo "Produtos ativos encontrados: " . count($listaProdutos) . "\n\n";
    
    if (empty($listaProdutos)) {
        echo "ERRO: Nenhum produto ativo encontrado!\n";
        echo "Verifique se há produtos cadastrados e se estão marcados como 'ativo'.\n";
    } else {
        foreach ($listaProdutos as $p) {
            echo "ID: {$p['id']}\n";
            echo "Título: {$p['titulo']}\n";
            echo "Preço: R$ " . number_format($p['preco'], 2, ',', '.') . "\n";
            echo "Ativo: " . ($p['ativo'] ? 'Sim' : 'Não') . "\n";
            echo "Link: " . ($p['link_pagina'] ?? 'N/A') . "\n";
            echo "---\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

