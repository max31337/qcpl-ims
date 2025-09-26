# QCPL-IMS Copilot Instructions

Laravel + Livewire (TALL) inventory for Quezon City Public Library. Use these project‑specific rules and workflows to stay productive.

## Architecture and Domains
- Core models: Asset, Supply, AssetTransferHistory, Branch, Division, Section, User (see GUIDE.md).
- Strict data isolation: always scope queries via Model::scopeForUser(User). Main-branch admins/observers see all; others are branch‑scoped.
- Assets denormalize location as current_branch_id/current_division_id/current_section_id; transfers must write a history row first.

## Livewire organization
- Components:
  - app/Http/Livewire/Assets/* (List, Form, Transfer, History, Reports, Analytics)
  - app/Http/Livewire/Supplies/* (List, Form, StockAdjustment, Reports, Analytics)
  - app/Http/Livewire/Admin/* (Users, Branches, Categories, ActivityLogs, Analytics)
- Debounce inputs: wire:model.debounce.300ms on filters/search.

## Identifiers and sequences
- Assets: year‑based sequence like 2025-0001 via Asset::generatePropertyNumber().
- Supplies: short code like SUP-001.

## Transfers and audit
- Move flow:
  1) Create AssetTransferHistory (origin/previous/current, transfer_date, remarks, transferred_by).
  2) Update asset current_* location fields.
- Log create/update/transfer events (see GUIDE.md).

## Reporting and analytics
- Exports: Maatwebsite\Excel (FromCollection + WithHeadings); always apply scopeForUser().
- Role dashboards: Admin (global), Staff (branch), Supply (stock/values), Property (asset status/value). See GUIDE.md Analytics examples.

## Frontend patterns
- Tailwind + Alpine.js. Status badges: green=active, yellow=condemn, red=disposed.
- Images: validate image|max:2048; store storage/app/public/assets; render via Storage::url().

## AuthZ conventions
- Use Policies/Gates + CheckRole middleware; avoid inline role checks in Blade.
- User helpers: isAdmin(), isSupplyOfficer(), isPropertyOfficer(), isObserver(), isMainBranch().

## Local workflows (PowerShell)
```powershell
# A) Existing clone (preferred)
composer install
copy .env.example .env
php artisan key:generate

# SQLite quick start (matches .env DB_CONNECTION=sqlite)
New-Item -ItemType Directory database -ErrorAction SilentlyContinue
New-Item -ItemType File database\database.sqlite -Force

npm install
npm run dev
php artisan migrate
php artisan storage:link
php artisan serve
```
```powershell
# B) New project init (only if starting fresh in empty dir)
composer create-project laravel/laravel .
composer require laravel/breeze --dev
php artisan breeze:install livewire
composer update -W
npm install && npm run dev
copy .env.example .env && php artisan key:generate
New-Item -ItemType Directory database -ErrorAction SilentlyContinue
New-Item -ItemType File database\database.sqlite -Force
php artisan migrate && php artisan storage:link && php artisan serve
```
Composer notes:
- Do not manually add livewire:^3.0. Breeze’s Livewire stack installs compatible Livewire + Volt.
- If Livewire/Volt conflicts occur: composer remove livewire/livewire livewire/volt, then composer update -W (or require -W "livewire/volt:^1.7" "livewire/livewire:^3.6").

When adding features, preserve branch/role scoping, write transfer histories before mutating locations, and follow the component placement conventions above.