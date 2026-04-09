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
- **Brand name**: valor de `APP_NAME` no .env (atualmente "Flag")

---

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

---

## Arquitetura de arquivos

### Models
```
app/Models/User.php         ← isSuperAdmin(), isAdmin(), isEditor(), isAtLeastAdmin()
                               getAgeAttribute(), getFilamentAvatarUrl(), canAccessPanel()
app/Models/Setting.php      ← get(key, default), set(key, value, group)
```

### Providers
```
app/Providers/AppServiceProvider.php              ← registra UserObserver
app/Providers/Filament/AdminPanelProvider.php     ← cor, brandName, userMenuItems→UserResource
                                                     render hook: user-info badge no topo
```

### Observers
```
app/Observers/UserObserver.php  ← deleted(): recria super_admin se não restar nenhum
```

### Policies
```
app/Policies/UserPolicy.php  ← viewAny, view, create, update, delete, deleteAny
                                baseado em isAtLeastAdmin() e comparação de IDs
```

### Filament Resources
```
app/Filament/Resources/UserResource.php
  └── Pages/
      ├── ListUsers.php    ← somente Admin+, header: CreateAction
      ├── CreateUser.php   ← somente Admin+, redirect para edit após criação
      └── EditUser.php     ← Admin+ ou próprio usuário; gerencia avatar antigo; notificação PT
```

### Filament Pages
```
app/Filament/Pages/EditProfile.php   ← redireciona para UserResource/edit/{id}
```

### Views
```
resources/views/
├── welcome.blade.php                          ← homepage pública (portfólio)
├── dashboard.blade.php                        ← dashboard autenticado (Breeze, desativado)
├── filament/
│   ├── components/user-info.blade.php         ← badge nome+nível no topo do painel
│   └── pages/edit-profile.blade.php           ← redirect para UserResource
├── auth/                                      ← views Breeze (instalado, rotas desativadas)
├── layouts/                                   ← app.blade.php, guest.blade.php
├── components/                                ← components Breeze
└── profile/                                   ← views de perfil Breeze
```

### Seeders
```
database/seeders/UserProfileSeeder.php   ← popula user (sem sobrescrever senha) + 7 grupos de settings
database/seeders/DatabaseSeeder.php      ← cria user de teste (test@example.com)
```

---

## Banco de dados

### Tabela `users` — todos os campos
```
id, name, email, password, email_verified_at, remember_token, created_at, updated_at,
avatar, title, role, subname, bio, birthdate, phone, location, website,
addr, district, zip, country, cnpj, ie, rs, razao_social, nome_fantasia,
links, github, linkedin, twitter, instagram, tiktok, youtube, facebook, fb_page,
medium, devto, codepen, behance, dribbble, deviantart, pinterest, locale, access_level
```

**Casts:** `email_verified_at` → datetime | `password` → hashed | `birthdate` → date

### Tabela `settings` — estrutura e grupos
Campos: `id, group, key (unique), value, created_at, updated_at`

- `identity` → site.name, site.subname, site.description, site.office, site.role, site.charset
- `seo` → seo.schema, seo.schema_og, seo.google_veri
- `assets` → assets.logotipo, assets.avatar, assets.image_share, assets.favicon, assets.cv
- `typography` → typography.font_name, typography.font_weight
- `personal` → personal.email, phone, addr, district, city, uf, zip, country, age, cnpj, ie
- `social` → todas as redes sociais
- `mail` → mail.from_address, mail.from_name

### Migrações (11 arquivos)
```
0001_01_01_000000  → users, password_reset_tokens, sessions
2026_03_26_005635  → adiciona avatar, title, bio, phone, location, website, github, linkedin, twitter, instagram, youtube, locale
2026_03_26_013128  → cria tabela settings
2026_03_26_014528  → placeholder vazio (ignorar)
2026_03_27_004158  → adiciona addr, district, zip, country, cnpj, ie, links, tiktok, facebook, medium, devto, codepen, behance, dribbble, deviantart, pinterest
2026_03_27_010056  → adiciona role, subname, birthdate, rs, fb_page
2026_03_27_010827  → adiciona razao_social, nome_fantasia
2026_03_31_010318  → adiciona access_level ENUM('super_admin','editor') default 'editor'
2026_03_31_012443  → atualiza ENUM para ('super_admin','admin','editor')
```

---

## UserResource — campos do formulário

Todos os campos têm `live(onBlur: true)` para auto-save.

| Seção | Campo | Tipo | Observações |
|---|---|---|---|
| Perfil e acesso | avatar | FileUpload | circular, editor de imagem, dir: avatars/, max 2MB |
| | access_level | Select/Badge | Select visível só para Admin+ editando outros |
| Pessoal | name, email, phone, birthdate | Text/Date | phone: máscara +55 (99) 999-999-999 |
| | title, role, subname, bio | Text/Textarea | |
| Endereço | addr, district, location, zip, country | Text | zip: máscara 99999-999 |
| Dados jurídicos | razao_social, nome_fantasia, cnpj, ie | Text | cnpj/ie com máscara |
| Redes principais | website, links, github, linkedin, twitter, instagram, tiktok, youtube, facebook, fb_page | URL | |
| Outras redes | medium, devto, codepen, behance, dribbble, deviantart, pinterest | URL | |
| Senha | current_password, password, password_confirmation | Password | current só ao editar próprio perfil |

**Tabela (ListUsers):** avatar, name, email, title, access_level (badge colorido), created_at

---

## Rotas (routes/web.php)
```php
GET  '/'          → view('welcome', compact('user'))   // portfólio público
GET  '/dashboard' → view('dashboard')                  // requer auth + verified
GET  '/profile'   → ProfileController@edit             // requer auth
PATCH '/profile'  → ProfileController@update
DELETE '/profile' → ProfileController@destroy
```
Rotas Breeze (auth.php) instaladas mas desativadas (comentado em web.php).

---

## .env — variáveis DAA_*
```
APP_NAME="Flag"
DAA_PANEL_COLOR=#e3000b
DAA_NAME="Darlis Alves Amorim"
DAA_EMAIL=eu@darlisalvesamorim.dev
DAA_PASSWORD=(definir manualmente — não tem valor padrão)
DAA_PHONE="+55 (11) 966-274-729"
DAA_OFFICE="Developer & Design"
DAA_ROLE="Software Engineer"
DAA_SUBNAME="Software Engineer and Full Stack Developer Freelancer..."
DAA_DESCRIPTION="Software Engineer and Full Stack Developer de São Paulo/SP"
DAA_AGE=29
DAA_CHARSET=UTF-8

# Endereço (atenção aos typos conhecidos)
DAA_ADDR="Av. Interlagos, 4944"
DAA_DISTRICT="Zona Sul"    # era "Zona SulL" — corrigir se ainda assim
DAA_CITY="São Paulo"
DAA_UF=SP                  # era "SPP" — corrigir se ainda assim
DAA_ZIP=04777-000
DAA_COUNTRY=Brasil

# Empresa
DAA_CNPJ, DAA_IE, DAA_RAZAO_SOCIAL, DAA_NOME_FANTASMA

# Assets
DAA_LOGOTIPO=logotipo.svg
DAA_AVATAR=avatar.svg
DAA_IMAGE_SHARE=default.svg
DAA_IMAGE_FAVICON=favicon.svg
DAA_CV=curriculo_darlisalvesamorim.pdf

# Tipografia
DAA_FONT_NAME="Roboto+Mono"
DAA_FONT_WEIGHT="100;200;300;400;500;600;700"

# Redes sociais — padrão: URL base + handle, concatenados no seeder
# Ex: DAA_GITHUB_URL=https://github.com/ + DAA_GITHUB=darlisamorim → https://github.com/darlisamorim
# Mesmo padrão para: LINKS, LINKEDIN, TWITTER, INSTAGRAM, TIKTOK, YOUTUBE,
#   FB, FB_PAGE, MEDIUM, DEVTO, CODEPEN, BEHANCE, DRIBBBLE, DEVIANTART, PINTEREST
```

---

## Navegação do painel
- "Meu Perfil" no avatar superior direito → `/admin/users/{id}/edit`
- Menu lateral "Usuários" → visível somente para Super Admin e Admin
- Editors acessam perfil pelo avatar ou pela URL direta
- Badge de nível (Super Admin / Admin / Editor) exibido via render hook no topo

---

## Dependências principais
```
composer:
  laravel/framework ^12.0
  filament/filament ^3.3
  laravel/tinker ^2.10.1
  laravel/breeze ^2.4 (instalado, rotas desativadas)

npm:
  vite ^7.0.7
  tailwindcss ^3.1.0
  alpinejs ^3.4.2
  laravel-vite-plugin ^2.0.0
```

---

## O que está funcionando ✅
- Filament instalado e configurado (cor, brand, menu)
- Sistema de usuários com 3 níveis hierárquicos completo
- UserResource com auto-save em todos os campos (onBlur)
- Upload e crop de avatar com rename automático (slug do nome)
- Super Admin protegido de deleção
- Hierarquia de permissões via UserPolicy
- "Meu Perfil" redireciona para UserResource para todos os níveis
- Seeder popula dados sem sobrescrever senha existente
- Botão "Resetar do .env" para Super Admin (sem sobrescrever senha)
- Settings table populada via Seeder (7 grupos)
- Placeholders e máscaras em todos os campos
- UserObserver recria Super Admin se todos forem deletados
- welcome.blade.php exibe dados do banco (portfólio público)
- Storage link criado: `public/storage → storage/app/public`

## Bugs pendentes
1. **"Meu Perfil" no menu lateral** — ainda aparece para alguns usuários (deveria sumir)
2. **Typos no .env** — verificar `DAA_DISTRICT` e `DAA_UF` ainda têm erros
3. **Campo "website"** do Super Admin mostra "test" — corrigir via painel

---

## Próximas funcionalidades (em ordem de prioridade)
1. **Blog** — Resource Filament: Posts, Categorias, Tags
2. **Projetos Open Source** — Resource + integração GitHub API
3. **Páginas** — Sobre Mim, Contato (editor rico)
4. **Mídia** — gerenciamento de imagens com SEO
5. **Ferramentas** — Analytics, SEO global, Open Graph
6. **Configurações** — E-mail, Tema, Identidade via painel
7. **Front-end público** — Home, Blog, Projetos, Contato, Sobre, Orçamento

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

---

## Comandos úteis
```bash
# Servidor
php artisan serve

# Banco
php artisan migrate
php artisan db:seed --class=UserProfileSeeder

# Cache
php artisan cache:clear && php artisan config:clear && php artisan view:clear

# Filament
php artisan make:filament-resource NomeResource --generate

# Debug
php artisan tinker

# Git
git add . && git commit -m "mensagem"
```

## Observações importantes
- Breeze instalado mas rotas desativadas (comentado em routes/web.php)
- Avatars salvos em: `storage/app/public/avatars/{slug-do-nome}.png`
- .env usa prefixo `DAA_` para todas as variáveis personalizadas
- `DAA_PASSWORD` não tem valor padrão — definir manualmente após deploy
- Session, Cache e Queue: driver `database` (não `file` nem `redis`)
- Timezone: `America/Sao_Paulo` | Locale: `pt_BR`
- **Nunca usar Claude Code em modo auto-accept — sempre usar "manually approve edits"**
