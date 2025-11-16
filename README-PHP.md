# Sistema PHP - Painel Admin de Produtos

Sistema completo de gerenciamento de produtos com painel administrativo para o site da Bianca Moraes.

## 📋 Estrutura do Projeto

```
/
├── admin/                    # Painel administrativo
│   ├── index.php            # Dashboard principal
│   ├── login.php            # Página de login
│   ├── logout.php           # Logout
│   └── produtos.php         # CRUD de produtos
├── includes/                 # Classes e funções reutilizáveis
│   ├── auth.php             # Sistema de autenticação
│   ├── produtos.php         # Gerenciamento de produtos
│   └── produtos_template.php # Template de renderização
├── uploads/                  # Diretório de uploads (criado automaticamente)
├── config.php               # Configurações do sistema
├── database.php             # Conexão com banco de dados
├── install.php              # Script de instalação
├── index-inicio.php         # Página inicial (versão PHP dinâmica)
└── index-inicio.html        # Página HTML original (mantida como backup)
```

## 🚀 Instalação

### 1. Configurar Banco de Dados

Edite o arquivo `config.php` e ajuste as credenciais do banco:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bianca_moraes');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 2. Executar Instalação

Acesse no navegador:
```
http://localhost/public_html(4)/install.php
```

Este script irá:
- Criar o banco de dados `bianca_moraes`
- Criar as tabelas `admins` e `produtos`
- Inserir o admin padrão (usuário: `admin`, senha: `admin123`)
- Inserir 5 produtos de exemplo

### 3. Acessar Painel Admin

Após a instalação, acesse:
```
http://localhost/public_html(4)/admin/login.php
```

**Credenciais padrão:**
- Usuário: `admin`
- Senha: `admin123`

⚠️ **IMPORTANTE:** Após a instalação, delete o arquivo `install.php` por segurança!

## 📱 Funcionalidades

### Painel Administrativo

- **Dashboard:** Visualização de estatísticas (total de produtos, ativos, inativos)
- **Gerenciamento de Produtos:**
  - Criar novos produtos
  - Editar produtos existentes
  - Excluir produtos
  - Ativar/desativar produtos
  - Ordenar produtos por ordem de exibição

### Campos do Produto

- Título
- Descrição
- Preço
- Imagem principal (URL ou caminho local)
- Localização (padrão: "São Paulo - SP")
- Data e hora de publicação
- Link da página do produto
- Badge "Garantia da OLX" (sim/não)
- Status (ativo/inativo)
- Ordem de exibição

### Página Inicial Dinâmica

O arquivo `index-inicio.php` carrega automaticamente os produtos ativos do banco de dados e os exibe na página inicial, mantendo o design original da OLX.

## 🔐 Segurança

- Senhas criptografadas com `password_hash()`
- Prepared statements em todas as queries SQL
- Proteção contra SQL Injection
- Validação de sessão com timeout
- Proteção do diretório admin com `.htaccess`

## 🛠️ Tecnologias Utilizadas

- **PHP 7.4+**
- **MySQL 5.7+**
- **PDO** para acesso ao banco
- **HTML5/CSS3** para interface
- **JavaScript** para interatividade

## 📝 Estrutura do Banco de Dados

### Tabela `admins`
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `username` (VARCHAR 50, UNIQUE)
- `email` (VARCHAR 100, UNIQUE)
- `password` (VARCHAR 255)
- `nome_completo` (VARCHAR 100)
- `created_at` (TIMESTAMP)
- `last_login` (TIMESTAMP)
- `active` (TINYINT 1)

### Tabela `produtos`
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `titulo` (VARCHAR 255)
- `descricao` (TEXT)
- `preco` (DECIMAL 10,2)
- `imagem_principal` (VARCHAR 255)
- `localizacao` (VARCHAR 100)
- `data_publicacao` (DATE)
- `hora_publicacao` (TIME)
- `garantia_olx` (TINYINT 1)
- `link_pagina` (VARCHAR 255)
- `ordem` (INT)
- `ativo` (TINYINT 1)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## 🔄 Migração do HTML para PHP

O sistema mantém o arquivo `index-inicio.html` original e cria `index-inicio.php` que:
1. Carrega o HTML original
2. Busca produtos ativos no banco
3. Substitui a seção estática de produtos por uma versão dinâmica
4. Mantém todo o resto do HTML intacto

## 📞 Suporte

Para alterar configurações, edite o arquivo `config.php`.

Para adicionar novos administradores, acesse o banco de dados diretamente ou crie um script de cadastro.

## ⚠️ Notas Importantes

1. **Backup:** Sempre faça backup do banco de dados antes de atualizações
2. **Senhas:** Altere a senha padrão do admin após a primeira instalação
3. **Uploads:** O diretório `uploads/` é criado automaticamente e protegido
4. **Produção:** Em produção, ajuste as configurações de sessão e segurança em `config.php`

