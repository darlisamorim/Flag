# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Initial setup
composer setup

# Run development servers (Laravel + Vite + queue + logs in parallel)
composer dev

# Run tests (uses in-memory SQLite)
composer test

# Run a single test file or filter
php artisan test --filter=TestClassName
php artisan test tests/Feature/ExampleTest.php

# Code style (Laravel Pint)
vendor/bin/pint

# Database
php artisan migrate
php artisan db:seed --class=UserProfileSeeder

# Asset compilation
npm run dev
npm run build
```

## Architecture

This is a **Laravel 12 + Filament 3 admin panel** — a personal profile/portfolio management system. The entire UI lives inside Filament at `/admin`.

### Key concepts

**Access control** is role-based via `access_level` on the `User` model: `super_admin`, `admin`, `editor`. Only `super_admin` and `admin` can access the Filament panel (`canAccessPanel`). The `UserObserver` auto-creates a super admin (from env vars) if the last one is deleted.

**User model** (`app/Models/User.php`) is the central entity — it stores not just auth data but the entire user profile: personal info, address, Brazilian legal data (CNPJ, IE, etc.), and 17+ social network URLs.

**Settings model** (`app/Models/Setting.php`) is a simple key/value store with groups (`identity`, `seo`, `assets`, `typography`, `mail`). Access via `Setting::get($key)` / `Setting::set($key, $value, $group)`.

**Filament panel** (`app/Providers/Filament/AdminPanelProvider.php`) — panel ID is `admin`, path is `/admin`. The "Meu Perfil" user menu item links directly to `UserResource::edit` for the authenticated user.

**UserResource** (`app/Filament/Resources/UserResource.php`) — the main (and currently only) Filament resource. All form fields use `->live(onBlur: true)->afterStateUpdated(fn ...)` to auto-save on blur via the private `autoSave()` helper. Super admins cannot be deleted or have their `access_level` changed through the UI.

### Environment variables

The `DAA_*` env vars populate the initial super admin user via `UserProfileSeeder`. Panel accent color is controlled by `DAA_PANEL_COLOR` (hex). Social URL fields follow a `DAA_*_URL` + `DAA_*` pattern (base URL + handle/path).

### Tests

Tests use SQLite in-memory (`DB_DATABASE=:memory:`). Test suites: `tests/Unit/` and `tests/Feature/`.

# CLAUDE.md — Projeto Flag (darlisalvesamorim.dev)

## Visão geral
Site pessoal/portfólio com painel administrativo completo.
Stack: Laravel 12 + Filament v3 + Blade + Tailwind CSS + MySQL

## Ambiente local
- **PHP**: 8.2.4 (XAMPP)
- **Composer**: 2.8.10
- **Node**: v24.14.1
- **Servidor local**: `php artisan serve` → http://127.0.0.1:8000
- **Banco local**: MySQL (XAMPP) → banco `flag`
- **Projeto**: `/Applications/XAMPP/xamppfiles/htdocs/Flag`

## Ambiente produção (Hostinger)
- **Domínio**: https://www.darlisalvesamorim.dev
- **SSH**: `ssh -p 65002 u310924194@147.79.85.200`
- **Projeto**: `/home/u310924194/domains/darlisalvesamorim.dev/public_html`
- **Banco**: u310924194_5lNnD / user: u310924194_Vf3DG

## Painel admin
- **URL local**: http://127.0.0.1:8000/admin
- **Login**: eu@darlisalvesamorim.dev
- **Cor primária**: `#e3000b` (vermelho) via `DAA_PANEL_COLOR` no .env

## Arquitetura do sistema

### Níveis de acesso (users.access_level)
- `super_admin` → acesso total, único que não pode ser deletado
- `admin` → pode editar/excluir somente editors
- `editor` → acessa o painel com limitações (em desenvolvimento)

### Arquivos principais já criados
```
app/
├── Models/
│   ├── User.php              ← com isSuperAdmin(), isAdmin(), isEditor(), isAtLeastAdmin()
│   └── Setting.php           ← get/set de configurações globais
├── Observers/
│   └── UserObserver.php      ← recria super_admin se todos forem deletados
├── Providers/
│   ├── AppServiceProvider.php ← registra UserObserver
│   └── Filament/
│       └── AdminPanelProvider.php ← cor, brandName, userMenuItems
├── Filament/
│   └── Resources/
│       ├── UserResource.php  ← CRUD usuários com auto-save e hierarquia
│       └── UserResource/Pages/
│           ├── ListUsers.php
│           ├── CreateUser.php
│           └── EditUser.php  ← proteção de hierarquia + senha atual para self
```

### Banco de dados — tabelas criadas
- `users` — com todos os campos de perfil (avatar, redes sociais, endereço, dados jurídicos)
- `settings` — chave/valor para configurações do site (group, key, value)
- `sessions`, `cache`, `jobs` — padrão Laravel

### Campos da tabela users
```
id, name, email, password, avatar, title, role, subname, bio, birthdate,
phone, location, addr, district, zip, country, cnpj, ie, rs, razao_social,
nome_fantasia, links, github, linkedin, twitter, instagram, tiktok, youtube,
facebook, fb_page, medium, devto, codepen, behance, dribbble, deviantart,
pinterest, website, locale, access_level, email_verified_at,
remember_token, created_at, updated_at
```

### Tabela settings — grupos existentes
- `identity` → site.name, site.subname, site.description, site.office, site.role, site.charset
- `seo` → seo.schema, seo.schema_og, seo.google_veri
- `assets` → assets.logotipo, assets.avatar, assets.image_share, assets.favicon, assets.cv
- `typography` → typography.font_name, typography.font_weight
- `mail` → mail.from_address, mail.from_name

## Seeders disponíveis
```bash
php artisan db:seed --class=UserProfileSeeder  # popula user + settings do .env
```

## .env — variáveis principais
```
APP_NAME="Darlis Alves Amorim"
DAA_PANEL_COLOR=#e3000b
DAA_NAME, DAA_EMAIL, DAA_PHONE, DAA_OFFICE, DAA_ROLE
DAA_ADDR, DAA_DISTRICT, DAA_CITY, DAA_UF, DAA_ZIP, DAA_COUNTRY
DAA_CNPJ, DAA_IE, DAA_RS, DAA_RAZAO_SOCIAL, DAA_NOME_FANTASMA
DAA_GITHUB_URL + DAA_GITHUB (padrão para todas as redes sociais)
DAA_LINKEDIN_URL + DAA_LINKEDIN
DAA_TWITTER_URL + DAA_TWITTER
... etc
```

## O que está funcionando ✅
- Filament instalado e configurado
- Sistema de usuários com 3 níveis de hierarquia
- UserResource com auto-save em todos os campos
- Upload de avatar com rename automático (slug do nome)
- Super Admin protegido de deleção
- Admin não pode editar Super Admin
- Settings table populada via Seeder
- Cor do painel via .env

## O que precisa ser corrigido/feito ❌

### URGENTE — Bugs pendentes
1. **Editor não consegue logar** — em `app/Models/User.php` mudar:
   ```php
   public function canAccessPanel(Panel $panel): bool
   {
       return true; // todos acessam, permissões por recurso
   }
   ```

2. **Cadastro de usuário** — ao criar usuário pelo painel, verificar se a senha está sendo salva com hash corretamente

3. **Auto-save duplo no UserResource** — ocasionalmente dispara duas notificações "Salvo!"

### Próximas funcionalidades a construir (em ordem)
1. **Blog** — Resource Filament: Posts, Categorias, Subcategorias, Tags
2. **Projetos Open Source** — Resource Filament + integração GitHub API
3. **Páginas** (Sobre Mim, Contato) — editor TinyMCE ou similar
4. **Mídia** — gerenciamento de imagens com campos SEO (alt, title, description)
5. **Ferramentas** — Analytics, SEO global, Open Graph
6. **Configurações** — E-mail (SMTP/Mailgun), Tema (cores, fontes), Identidade
7. **Front-end público** — Blade + Tailwind (Home, Blog, Projetos, Contato, Sobre, Orçamento)

## Mapa do sistema (menu do painel)
```
Painel de controle
├── Usuários          ← feito (só super_admin e admin)
├── Blog              ← a fazer
├── Projetos          ← a fazer
├── Mídia             ← a fazer
├── Ferramentas       ← a fazer
└── Configurações     ← a fazer
```

## Comandos úteis
```bash
php artisan serve                           # inicia servidor local
php artisan migrate                         # roda migrations
php artisan db:seed --class=UserProfileSeeder
php artisan cache:clear && php artisan config:clear && php artisan view:clear
php artisan make:filament-resource NomeResource --generate
php artisan make:migration create_xxx_table
php artisan tinker
```

## Observações importantes
- O Breeze está instalado mas com rotas desativadas (comentado em `routes/web.php`)
- O `php artisan serve` é necessário localmente (XAMPP não tem Node/Vite configurado para subpasta)
- Storage link já criado: `public/storage` → `storage/app/public`
- Avatars ficam em `storage/app/public/avatars/` com nome `slug-do-usuario.png`
- O `.env` usa prefixo `DAA_` para variáveis personalizadas do projeto
