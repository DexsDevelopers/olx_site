# 🔧 Solução para Erro de Deploy - Autenticação GitHub

## Erro Encontrado
```
clone: fatal: could not read Username for 'https://github.com': No such device or address
```

## ✅ Soluções

### **Solução 1: Personal Access Token (Recomendado)**

1. **Criar Token no GitHub:**
   - Acesse: https://github.com/settings/tokens
   - Clique em "Generate new token" → "Generate new token (classic)"
   - Nome: `Deploy Token`
   - Expiração: Escolha conforme necessário
   - Permissões: Marque `repo` (acesso completo a repositórios)
   - Clique em "Generate token"
   - **COPIE O TOKEN** (você só verá uma vez!)

2. **Usar no Sistema de Deploy:**
   - No campo de URL do repositório, use:
   ```
   https://SEU_TOKEN_AQUI@github.com/DexsDevelopers/olx_site.git
   ```
   - Substitua `SEU_TOKEN_AQUI` pelo token que você copiou

### **Solução 2: SSH (Alternativa)**

1. **Gerar Chave SSH no Servidor:**
   ```bash
   ssh-keygen -t ed25519 -C "deploy@olx_site"
   ```

2. **Copiar Chave Pública:**
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```

3. **Adicionar no GitHub:**
   - Acesse: https://github.com/settings/keys
   - Clique em "New SSH key"
   - Cole a chave pública
   - Salve

4. **Usar URL SSH no Deploy:**
   ```
   git@github.com:DexsDevelopers/olx_site.git
   ```

### **Solução 3: Tornar Repositório Público**

Se o repositório for público, o clone HTTPS funciona sem autenticação:
- Acesse: https://github.com/DexsDevelopers/olx_site/settings
- Role até "Danger Zone"
- Clique em "Change visibility" → "Make public"

⚠️ **Atenção:** Isso torna o código público!

## 📋 Configuração por Sistema de Deploy

### **Hostinger/cPanel:**
- Vá em "Git Version Control" ou "Deploy"
- Use a URL com token: `https://TOKEN@github.com/DexsDevelopers/olx_site.git`

### **Vercel/Netlify:**
- Conecte via interface web (autenticação automática)
- Ou use variável de ambiente `GITHUB_TOKEN`

### **Outros:**
- Configure credenciais na seção de "Repository Settings" ou "Deployment Settings"

## 🔒 Segurança

- **NUNCA** commite tokens ou senhas no código
- Use variáveis de ambiente quando possível
- Revogue tokens antigos regularmente
- Use tokens com permissões mínimas necessárias

