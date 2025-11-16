# 🔧 Troubleshooting - Produtos Não Atualizam

## Problema
As mudanças feitas no painel admin não aparecem na página inicial nem nas páginas de produtos.

## Passos para Diagnosticar

### 1. Verificar se o PHP está funcionando

Acesse: `https://seu-site.com/test-produtos.php`

**O que verificar:**
- Se aparecer uma lista de produtos → PHP está funcionando ✅
- Se aparecer erro ou página em branco → Problema de configuração PHP ❌
- Se baixar o arquivo → PHP não está habilitado ❌

### 2. Verificar produtos no banco de dados

Acesse: `https://seu-site.com/debug-produtos.php`

**O que verificar:**
- Quantos produtos estão cadastrados
- Quais estão marcados como "Ativo"
- Se os dados estão corretos (título, preço, etc.)

### 3. Verificar se está acessando a página PHP correta

**❌ ERRADO:**
- `https://seu-site.com/index-inicio.html` (HTML estático)
- `https://seu-site.com/index.html` (HTML estático)

**✅ CORRETO:**
- `https://seu-site.com/index-inicio.php` (PHP dinâmico)
- `https://seu-site.com/` (deve redirecionar para PHP)

### 4. Limpar cache do navegador

**Chrome/Edge:**
1. Pressione `Ctrl + Shift + Delete`
2. Selecione "Imagens e arquivos em cache"
3. Clique em "Limpar dados"

**Ou force atualização:**
- `Ctrl + F5` (Windows)
- `Cmd + Shift + R` (Mac)

### 5. Verificar se o .htaccess está funcionando

Se ao acessar `index-inicio.html` não redirecionar para `index-inicio.php`, o `.htaccess` pode não estar funcionando.

**Soluções:**
1. Verificar se o servidor suporta `.htaccess` (Hostinger/cPanel geralmente sim)
2. Verificar se `mod_rewrite` está habilitado
3. Tentar acessar diretamente: `index-inicio.php`

### 6. Verificar logs de erro

Verifique os logs do servidor para erros PHP:
- Painel Hostinger → Logs
- Ou verifique `error_log` no servidor

## Soluções Comuns

### Solução 1: Acessar diretamente o PHP

Em vez de `index-inicio.html`, sempre use:
```
https://seu-site.com/index-inicio.php
```

### Solução 2: Verificar se os produtos estão ativos

1. Acesse o painel admin: `/admin/index.php`
2. Verifique se os produtos têm o checkbox "Produto Ativo" marcado
3. Se não estiver, edite e marque como ativo

### Solução 3: Verificar link_pagina

No painel admin, cada produto deve ter o campo "Link da Página do Produto" preenchido:
- Exemplo: `index-iphone.html`
- Isso permite que `produto.php` encontre o produto correto

### Solução 4: Testar em modo anônimo/privado

Abra uma janela anônima/privada e acesse:
```
https://seu-site.com/index-inicio.php
```

Se funcionar em modo anônimo, é problema de cache do navegador.

### Solução 5: Verificar conexão com banco de dados

Edite `config.php` e verifique se as credenciais estão corretas:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u853242961_teste_site');
define('DB_USER', 'u853242961_usuario2');
define('DB_PASS', 'Lucastav8012@');
```

## Checklist Rápido

- [ ] Acessei `index-inicio.php` (não `.html`)
- [ ] Limpei o cache do navegador (`Ctrl + F5`)
- [ ] Verifiquei `test-produtos.php` e mostra produtos
- [ ] Verifiquei `debug-produtos.php` e os produtos estão ativos
- [ ] Os produtos no painel admin estão marcados como "Ativo"
- [ ] Testei em modo anônimo/privado
- [ ] Verifiquei os logs de erro do servidor

## Se Nada Funcionar

1. **Verifique os logs:**
   - Acesse o painel do Hostinger
   - Vá em "Logs" ou "Error Log"
   - Procure por erros relacionados a PHP ou banco de dados

2. **Teste a conexão com banco:**
   - Crie um arquivo `test-db.php`:
   ```php
   <?php
   require_once 'config.php';
   require_once 'database.php';
   $db = Database::getInstance();
   echo "Conexão OK!";
   ```

3. **Contate o suporte:**
   - Se o problema persistir, pode ser configuração do servidor
   - Compartilhe os resultados dos testes acima

## Arquivos de Debug Criados

- `test-produtos.php` - Testa se produtos estão sendo carregados
- `debug-produtos.php` - Mostra todos os produtos do banco em formato HTML
- `TROUBLESHOOTING.md` - Este arquivo

