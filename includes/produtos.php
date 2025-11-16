<?php
/**
 * Gerenciamento de Produtos
 */
require_once __DIR__ . '/../database.php';

class Produtos {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function listar($apenasAtivos = false) {
        $sql = "SELECT * FROM produtos";
        if ($apenasAtivos) {
            $sql .= " WHERE ativo = 1";
        }
        $sql .= " ORDER BY ordem ASC, id DESC";
        return $this->db->fetchAll($sql);
    }

    public function buscar($id) {
        return $this->db->fetchOne("SELECT * FROM produtos WHERE id = :id", ['id' => $id]);
    }

    public function criar($dados) {
        $sql = "INSERT INTO produtos (titulo, descricao, preco, imagem_principal, localizacao, data_publicacao, hora_publicacao, garantia_olx, link_pagina, ordem, ativo) 
                VALUES (:titulo, :descricao, :preco, :imagem_principal, :localizacao, :data_publicacao, :hora_publicacao, :garantia_olx, :link_pagina, :ordem, :ativo)";
        
        $params = [
            'titulo' => $dados['titulo'] ?? '',
            'descricao' => $dados['descricao'] ?? '',
            'preco' => floatval($dados['preco'] ?? 0),
            'imagem_principal' => $dados['imagem_principal'] ?? '',
            'localizacao' => $dados['localizacao'] ?? 'São Paulo - SP',
            'data_publicacao' => $dados['data_publicacao'] ?? date('Y-m-d'),
            'hora_publicacao' => $dados['hora_publicacao'] ?? '00:15:00',
            'garantia_olx' => isset($dados['garantia_olx']) ? 1 : 0,
            'link_pagina' => $dados['link_pagina'] ?? '',
            'ordem' => intval($dados['ordem'] ?? 0),
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        $this->db->query($sql, $params);
        return $this->db->getConnection()->lastInsertId();
    }

    public function atualizar($id, $dados) {
        $sql = "UPDATE produtos SET 
                titulo = :titulo,
                descricao = :descricao,
                preco = :preco,
                imagem_principal = :imagem_principal,
                localizacao = :localizacao,
                data_publicacao = :data_publicacao,
                hora_publicacao = :hora_publicacao,
                garantia_olx = :garantia_olx,
                link_pagina = :link_pagina,
                ordem = :ordem,
                ativo = :ativo
                WHERE id = :id";

        $params = [
            'id' => intval($id),
            'titulo' => $dados['titulo'] ?? '',
            'descricao' => $dados['descricao'] ?? '',
            'preco' => floatval($dados['preco'] ?? 0),
            'imagem_principal' => $dados['imagem_principal'] ?? '',
            'localizacao' => $dados['localizacao'] ?? 'São Paulo - SP',
            'data_publicacao' => $dados['data_publicacao'] ?? date('Y-m-d'),
            'hora_publicacao' => $dados['hora_publicacao'] ?? '00:15:00',
            'garantia_olx' => isset($dados['garantia_olx']) ? 1 : 0,
            'link_pagina' => $dados['link_pagina'] ?? '',
            'ordem' => intval($dados['ordem'] ?? 0),
            'ativo' => isset($dados['ativo']) ? 1 : 0
        ];

        try {
            $result = $this->db->query($sql, $params);
            // Log para debug (remover em produção se necessário)
            error_log("Produto atualizado - ID: $id, Título: " . $params['titulo']);
            return $result;
        } catch (Exception $e) {
            error_log("Erro ao atualizar produto ID $id: " . $e->getMessage());
            throw $e;
        }
    }

    public function deletar($id) {
        return $this->db->query("DELETE FROM produtos WHERE id = :id", ['id' => $id]);
    }

    public function formatarPreco($preco) {
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }

    public function formatarData($data, $hora = '00:15:00') {
        $timestamp = strtotime($data . ' ' . $hora);
        return date('d/m/Y, H:i', $timestamp);
    }
}

