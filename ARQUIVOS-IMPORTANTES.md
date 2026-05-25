# 📁 Arquivos Importantes para Deploy na Hostinger

## ✅ Arquivos Essenciais (DEVEM estar no servidor)

### Configuração
- `config.php` - Configurações do banco de dados
- `database.php` - Conexão com banco
- `.htaccess` - Configurações do servidor

### Páginas Principais
- `index-inicio.php` - **Página inicial dinâmica (IMPORTANTE!)**
- `index-inicio.html` - Template HTML base
- `produto.php` - Página dinâmica de produtos individuais

### Sistema Admin
- `admin/index.php` - Dashboard do painel admin
- `admin/login.php` - Login do admin
- `admin/produtos.php` - CRUD de produtos
- `admin/logout.php` - Logout

### Classes e Includes
- `includes/auth.php` - Sistema de autenticação
- `includes/produtos.php` - Gerenciamento de produtos
- `includes/produtos_template.php` - Template de renderização

### Páginas HTML de Produtos
- `index-iphone.html`
- `index-airfry.html`
- `index-cama.html`
- `index-maquina-de-lavar.html`
- `index.html`

### Arquivos de Debug (Opcional, mas útil)
- `check.php` - Teste básico de PHP
- `produtos-debug.php` - Ver produtos no banco
- `test-produtos.php` - Teste de conexão

### CSS e Assets
- `css/ds-tokens.css`
- `css/olx-reset.min.css`
- `images/` - Todas as imagens

## ⚠️ Arquivos que NÃO precisam estar no servidor

- `*.md` - Documentação (opcional)
- `test-*.php` - Arquivos de teste (opcional)
- `debug-*.php` - Arquivos de debug (opcional)
- `.git/` - Controle de versão
- `Downloads/` - Arquivos de backup local

## 🚀 Como Fazer Deploy

1. **Via Git (Recomendado):**
   - Configure o Git deploy na Hostinger
   - Ou faça clone do repositório no servidor

2. **Via FTP/File Manager:**
   - Faça upload de todos os arquivos listados acima
   - Certifique-se de manter a estrutura de pastas

3. **Verificar após deploy:**
   - Acesse `check.php` para verificar se PHP funciona
   - Acesse `produtos-debug.php` para verificar produtos
   - Acesse `index-inicio.php` para ver a página principal

