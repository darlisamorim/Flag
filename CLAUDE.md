# CLAUDE.md — Projeto Flag (darlisalvesamorim.dev)
_Atualizado: 08/04/2026_

## Visão geral
Site pessoal/portfólio com painel administrativo completo.
Stack: Laravel 12 + Filament v3 + Blade + Tailwind CSS + MySQL

## Ambiente local
- **PHP**: 8.2.4 (XAMPP)
- **Composer**: 2.8.10
- **Node**: v24.14.1
- **Servidor local**: `php artisan serve` → http://127.0.0.1:8000
- **Banco local**: MySQL (XAMPP) → banco `flag`, root sem senha
- **Projeto**: `/Applications/XAMPP/xamppfiles/htdocs/Flag`

## Ambiente produção (Hostinger)
- **Domínio**: https://www.darlisalvesamorim.dev
- **SSH**: `ssh -p 65002 u310924194@147.79.85.200`
- **Projeto**: `/home/u310924194/domains/darlisalvesamorim.dev/public_html`

## Painel admin
- **URL local**: http://127.0.0.1:8000/admin
- **Login Super Admin**: eu@darlisalvesamorim.dev
- **Cor primária**: `#e3000b` via `DAA_PANEL_COLOR` no .env

## Hierarquia de usuários

### Níveis (users.access_level)
- `super_admin` → acesso total
- `admin` → edita/exclui editors, edita próprio perfil
- `editor` → edita apenas o próprio perfil

### Regras de permissão
- Super Admin edita/exclui Admin e Editor
- Super Admin NÃO edita outro Super Admin (apenas vê)
- Admin edita/exclui Editor e próprio perfil
- Admin NÃO edita outro Admin nem Super Admin
- Editor edita apenas o próprio perfil
- Ninguém pode excluir Super Admin
- Se todos os usuários forem deletados, UserObserver recria o Super Admin

### Campos de senha por contexto
- **Logado na própria conta** → Senha atual + Nova senha + Confirmação
- **Super Admin / Admin editando outro usuário** → Nova senha + Confirmação (sem senha atual)

### Botão "Resetar do .env"
- Aparece APENAS para Super Admin editando o próprio perfil
- Atualiza todos os dados do perfil MAS não sobrescreve a senha

## Arquivos principais

### Models
```
app/Models/User.php         ← isSuperAdmin(), isAdmin(), isEditor(), isAtLeastAdmin()
app/Models/Setting.php      ← get/set configurações globais
```

### Providers
```
app/Providers/AppServiceProvider.php              ← registra UserObserver
app/Providers/Filament/AdminPanelProvider.php     ← cor, brandName, userMenuItems→UserResource
```

### Observers
```
app/Observers/UserObserver.php  ← recria super_admin se todos deletados
```

### Filament Resources
```
app/Filament/Resources/UserResource.php                    ← CRUD com auto-save e hierarquia
app/Filament/Resources/UserResource/Pages/ListUsers.php
app/Filament/Resources/UserResource/Pages/CreateUser.php
app/Filament/Resources/UserResource/Pages/EditUser.php     ← proteção hierarquia + reset .env
```

## Banco de dados

### Tabela users — campos
```
id, name, email, password, avatar, title, role, subname, bio, birthdate,
phone, location, addr, district, zip, country,
cnpj, ie, razao_social, nome_fantasia,
links, github, linkedin, twitter, instagram, tiktok, youtube,
facebook, fb_page, medium, devto, codepen, behance, dribbble, deviantart, pinterest,
website, locale, access_level, email_verified_at, remember_token, created_at, updated_at
```

### Tabela settings — grupos
- `identity` → site.name, site.subname, site.description, site.office, site.role, site.charset
- `seo` → seo.schema, seo.schema_og, seo.google_veri
- `assets` → assets.logotipo, assets.avatar, assets.image_share, assets.favicon, assets.cv
- `typography` → typography.font_name, typography.font_weight
- `personal` → personal.email, phone, addr, district, city, uf, zip, country, age, cnpj, ie
- `social` → todas as redes sociais
- `mail` → mail.from_address, mail.from_name

## Seeders
```bash
php artisan db:seed --class=UserProfileSeeder  # popula user + settings do .env
# IMPORTANTE: Seeder NUNCA sobrescreve a senha se o usuário já existe
```

## .env — variáveis principais
```
APP_NAME="Darlis Alves Amorim"
DAA_PANEL_COLOR=#e3000b
DAA_NAME, DAA_EMAIL, DAA_PHONE, DAA_OFFICE, DAA_ROLE, DAA_SUBNAME, DAA_DESCRIPTION
DAA_ADDR, DAA_DISTRICT, DAA_CITY, DAA_UF=SP, DAA_ZIP, DAA_COUNTRY
DAA_CNPJ, DAA_IE, DAA_RAZAO_SOCIAL, DAA_NOME_FANTASMA
# Redes sociais — URL base + handle separados, concatenados no seeder:
DAA_GITHUB_URL=https://github.com/ + DAA_GITHUB=darlisamorim → https://github.com/darlisamorim
# Mesmo padrão para: LINKEDIN, TWITTER, INSTAGRAM, TIKTOK, YOUTUBE, FB, FB_PAGE,
# MEDIUM, DEVTO, CODEPEN, BEHANCE, DRIBBBLE, DEVIANTART, PINTEREST, LINKS
```

## Navegação do painel
- "Meu Perfil" no avatar superior direito → redireciona para /admin/users/ID/edit
- Menu lateral "Usuários" → visível apenas para Super Admin e Admin
- Editors acessam o perfil pelo avatar ou pela URL /admin/users/ID/edit

## O que está funcionando ✅
- Filament instalado e configurado
- Sistema de usuários com 3 níveis hierárquicos
- UserResource com auto-save em todos os campos (onBlur)
- Upload de avatar com rename automático (slug do nome)
- Super Admin protegido de deleção
- Hierarquia de permissões implementada e testada
- "Meu Perfil" redireciona para UserResource para todos os níveis
- Seeder popula dados sem sobrescrever senha
- Botão "Resetar do .env" para Super Admin
- Settings table populada via Seeder
- Placeholders e máscaras em todos os campos

## Pendências e próximos passos ❌

### Bugs pendentes
1. **"Meu Perfil" no menu lateral** — ainda aparece para alguns usuários, deveria sumir
2. **Typos no .env** — `DAA_DISTRICT="Zona SulL"` e `DAA_UF=SPP` — corrigir manualmente
3. **Campo "Site pessoal"** do Super Admin mostra "test" — corrigir via painel

### Próximas funcionalidades (em ordem)
1. **Testar welcome.blade.php** com dados do banco — rota já atualizada
2. **Blog** — Resource Filament: Posts, Categorias, Tags
3. **Projetos Open Source** — Resource + GitHub API
4. **Páginas** — Sobre Mim, Contato (editor rico)
5. **Mídia** — gerenciamento de imagens com SEO
6. **Ferramentas** — Analytics, SEO global, Open Graph
7. **Configurações** — E-mail, Tema, Identidade
8. **Front-end público** — Home, Blog, Projetos, Contato, Sobre, Orçamento

## Mapa do painel
```
Painel de controle
├── Usuários          ← feito (super_admin e admin)
├── Blog              ← a fazer
├── Projetos          ← a fazer
├── Mídia             ← a fazer
├── Ferramentas       ← a fazer
└── Configurações     ← a fazer
```

## Comandos úteis
```bash
php artisan serve
php artisan migrate
php artisan db:seed --class=UserProfileSeeder
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan make:filament-resource NomeResource --generate
php artisan tinker
git add . && git commit -m "mensagem"
```

## Observações importantes
- Breeze instalado mas rotas desativadas (comentado em routes/web.php)
- Storage link criado: public/storage → storage/app/public
- Avatars: storage/app/public/avatars/slug-do-usuario.png
- .env usa prefixo DAA_ para variáveis personalizadas
- Nunca usar Claude Code em modo auto-accept — sempre usar "manually approve edits"
