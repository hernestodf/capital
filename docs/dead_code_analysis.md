# SisLoc - Dead Code Analysis Report

**Generated:** 2026-04-20
**Working Directory:** `/var/www/html/sisloc`
**Scope:** Views, Routes, Controllers, and cross-referenced dead code

---

## 1. Inventory

### 1.1 All Views (24 files)

| # | File Path | Size |
|---|-----------|------|
| 1 | `views/admin/index.php` | 3.6 KB |
| 2 | `views/auth/login.php` | 17.9 KB |
| 3 | `views/ai/agents.php` | 6.3 KB |
| 4 | `views/ai/agent-detail.php` | 2.9 KB |
| 5 | `views/ai/dashboard.php` | 11.8 KB |
| 6 | `views/ai/learning.php` | 19.7 KB |
| 7 | `views/cliente/create.php` | 9.1 KB |
| 8 | `views/cliente/edit.php` | 9.0 KB |
| 9 | `views/cliente/index.php` | 6.3 KB |
| 10 | `views/configuracoes/index.php` | 15.5 KB |
| 11 | `views/configuracoes/permissoes.php` | 12.4 KB |
| 12 | `views/dashboard/administrador.php` | 5.9 KB |
| 13 | `views/dashboard/estoquista.php` | 7.9 KB |
| 14 | `views/dashboard/produtor.php` | 6.3 KB |
| 15 | `views/errors/404.php` | 2.9 KB |
| 16 | `views/errors/500.php` | 3.1 KB |
| 17 | `views/home/index.php` | 7.1 KB |
| 18 | `views/layout/footer.php` | 1.3 KB |
| 19 | `views/layout/header.php` | 5.9 KB |
| 20 | `views/layout/sidebar.php` | 5.6 KB |
| 21 | `views/user/create.php` | 5.5 KB |
| 22 | `views/user/edit.php` | 5.4 KB |
| 23 | `views/user/index.php` | 6.3 KB |
| 24 | `views/user/show.php` | 5.0 KB |

### 1.2 All Routes (public/index.php)

| # | Method | Route | Controller@Method | Middleware |
|---|--------|-------|-------------------|------------|
| 1 | GET | `/test` | HomeController@test | None |
| 2 | GET | `/test/crud/users` | TestController@testUserCrud | None |
| 3 | GET | `/test/crud/clientes` | TestController@testClienteCrud | None |
| 4 | GET | `/test/crud/empresa` | TestController@testEmpresaCrud | None |
| 5 | GET | `/test/database` | TestController@testDatabase | None |
| 6 | GET | `/` | (anonymous redirect) | None |
| 7 | GET | `/auth/login` | AuthController@login | None |
| 8 | POST | `/auth/login` | AuthController@doLogin | None |
| 9 | GET | `/auth/logout` | AuthController@logout | None |
| 10 | POST | `/auth/logout` | AuthController@logout | None |
| 11 | GET | `/admin/` | AdminController@index | AuthMiddleware |
| 12 | GET | `/admin/dashboard` | AdminController@index | AuthMiddleware |
| 13 | GET | `/users/` | UserController@index | AuthMiddleware |
| 14 | GET | `/users/create` | UserController@create | AuthMiddleware |
| 15 | POST | `/users/store` | UserController@store | AuthMiddleware |
| 16 | GET | `/users/edit/{id}` | UserController@edit | AuthMiddleware |
| 17 | POST | `/users/update/{id}` | UserController@update | AuthMiddleware |
| 18 | GET | `/users/show/{id}` | UserController@show | AuthMiddleware |
| 19 | POST | `/users/delete/{id}` | UserController@delete | AuthMiddleware |
| 20 | POST | `/users/toggle/{id}` | UserController@toggle | AuthMiddleware |
| 21 | GET | `/clientes/` | ClienteController@index | AuthMiddleware |
| 22 | GET | `/clientes/create` | ClienteController@create | AuthMiddleware |
| 23 | POST | `/clientes/store` | ClienteController@store | AuthMiddleware |
| 24 | GET | `/clientes/edit/{id}` | ClienteController@edit | AuthMiddleware |
| 25 | POST | `/clientes/update/{id}` | ClienteController@update | AuthMiddleware |
| 26 | POST | `/clientes/delete/{id}` | ClienteController@delete | AuthMiddleware |
| 27 | POST | `/clientes/toggle/{id}` | ClienteController@toggle | AuthMiddleware |
| 28 | GET | `/configuracoes/` | ConfiguracoesController@index | AuthMiddleware |
| 29 | POST | `/configuracoes/update` | ConfiguracoesController@update | AuthMiddleware |
| 30 | POST | `/configuracoes/update-roles` | ConfiguracoesController@updateRoles | AuthMiddleware |
| 31 | GET | `/permissoes/` | PermissaoController@index | AuthMiddleware |
| 32 | POST | `/permissoes/update-roles` | PermissaoController@updateRoles | AuthMiddleware |
| 33 | GET | `/permissoes/create` | PermissaoController@create | AuthMiddleware |
| 34 | POST | `/permissoes/store` | PermissaoController@store | AuthMiddleware |
| 35 | GET | `/permissoes/edit/{id}` | PermissaoController@edit | AuthMiddleware |
| 36 | POST | `/permissoes/update/{id}` | PermissaoController@update | AuthMiddleware |
| 37 | POST | `/permissoes/delete/{id}` | PermissaoController@delete | AuthMiddleware |
| 38 | GET | `/dashboard/` | DashboardController@index | AuthMiddleware |
| 39 | GET | `/dashboard/administrador` | DashboardController@administrador | AuthMiddleware |
| 40 | GET | `/dashboard/produtor` | DashboardController@produtor | AuthMiddleware |
| 41 | GET | `/dashboard/estoquista` | DashboardController@estoquista | AuthMiddleware |
| 42 | GET | `/ai/` | AIController@dashboard | AuthMiddleware |
| 43 | GET | `/ai/dashboard` | AIController@dashboard | AuthMiddleware |
| 44 | GET | `/ai/agents` | AIController@agents | AuthMiddleware |
| 45 | GET | `/ai/agent/{id}` | AIController@agentDetail | AuthMiddleware |
| 46 | GET | `/ai/learning` | AIController@learning | AuthMiddleware |
| 47 | POST | `/ai/execute` | AIController@execute | AuthMiddleware |
| 48 | POST | `/ai/orchestrate` | AIController@orchestrate | AuthMiddleware |
| 49 | GET | `/api/ai/health` | AIController@apiHealth | None |
| 50 | GET | `/api/ai/stats` | AIController@apiStats | None |
| 51 | GET | `/api/ai/agents` | AIController@apiAgents | None |
| 52 | POST | `/api/ai/execute` | AIController@apiExecute | None |
| 53 | POST | `/api/ai/orchestrate` | AIController@apiOrchestrate | None |
| 54 | GET | `/api/ai/patterns` | AIController@apiGetPatterns | None |
| 55 | POST | `/api/ai/learn/pattern` | AIController@apiLearnPattern | None |
| 56 | POST | `/api/ai/learn/metric` | AIController@apiLearnMetric | None |
| 57 | GET | `/api/ai/feedbacks` | AIController@apiGetFeedbacks | None |
| 58 | POST | `/api/ai/learn/feedback` | AIController@apiLearnFeedback | None |
| 59 | POST | `/api/ai/learn/interaction` | AIController@apiLearnInteraction | None |
| 60 | GET | `/api/ai/preferences` | AIController@apiGetPreferences | None |
| 61 | POST | `/api/ai/learn/preference` | AIController@apiLearnPreference | None |

### 1.3 All Controllers (10 files)

| # | File | Size | Used by Routes? |
|---|------|------|-----------------|
| 1 | `src/Controllers/AdminController.php` | 340 B | Yes (`/admin/*`) |
| 2 | `src/Controllers/AIController.php` | 7.9 KB | Yes (`/ai/*`, `/api/ai/*`) |
| 3 | `src/Controllers/AuthController.php` | 1.8 KB | Yes (`/auth/*`) |
| 4 | `src/Controllers/ClienteController.php` | 7.4 KB | Yes (`/clientes/*`) |
| 5 | `src/Controllers/ConfiguracoesController.php` | 4.4 KB | Yes (`/configuracoes/*`) |
| 6 | `src/Controllers/DashboardController.php` | 4.9 KB | Yes (`/dashboard/*`) |
| 7 | `src/Controllers/HomeController.php` | 1.2 KB | Yes (`/test`) |
| 8 | `src/Controllers/PermissaoController.php` | 4.8 KB | Yes (`/permissoes/*`) |
| 9 | `src/Controllers/TestController.php` | 13.5 KB | Yes (`/test/crud/*`) |
| 10 | `src/Controllers/UserController.php` | 4.8 KB | Yes (`/users/*`) |

Note: `src/Http/ErrorController.php` exists but is invoked programmatically by the error handler, not by routes.

---

## 2. Cross-Reference Findings

### 2.1 Orphan Views (views NOT referenced by any controller)

| # | View File | Recommendation | Priority |
|---|-----------|---------------|----------|
| 1 | **`views/home/index.php`** | The controller method `HomeController::index()` exists but **no route points to it**. The route `/` redirects to login or dashboard. The only route using HomeController is `/test` which calls `test()`, not `index()`. The view follows the OLD pattern (includes topbar/sidebar separately, duplicating what header.php already includes). | **HIGH** |
| 2 | **`views/cliente/show.php`** | Referenced by `ClienteController::show()` (line 178), but **no route exists** for `/clientes/show/{id}`. The route was never registered in `public/index.php`. | **HIGH** |

### 2.2 Views with Broken Layout Pattern

| # | View File | Issue | Recommendation | Priority |
|---|-----------|-------|----------------|----------|
| 1 | **`views/home/index.php`** | Includes `<header id="topbar">`, `sidebar.php`, `<div id="main">`, `<div class="content">` AFTER `header.php` -- this DUPLICATES elements that `header.php` already includes. Violates the mandatory view pattern documented in AGENTS.md. | **Refactor or Remove** | **HIGH** |

### 2.3 Missing Views (controllers reference views that do not exist)

| # | Controller Method | Missing View File | Recommendation | Priority |
|---|-------------------|-------------------|----------------|----------|
| 1 | `PermissaoController::create()` (line 64) | `views/configuracoes/permissao_create.php` | The view file does NOT exist on disk. Creating a new permission requires this view. | **HIGH** |
| 2 | `PermissaoController::edit()` (line 106) | `views/configuracoes/permissao_edit.php` | The view file does NOT exist on disk. Editing a permission requires this view. | **HIGH** |
| 3 | `ClienteController::show()` (line 178) | `views/cliente/show.php` | **DOES NOT EXIST** -- Glob search returned no results for this file. | **HIGH** |

### 2.4 Duplicate/Redundant Routes

| # | Routes | Issue | Recommendation | Priority |
|---|--------|-------|----------------|----------|
| 1 | `/permissoes/*` (7 routes) vs `/configuracoes/*` (3 routes) | **The `/permissoes` group is functionally redundant.** The sidebar links to `/configuracoes/permissoes` (not `/permissoes`). The `configuracoes/index.php` view ALREADY contains a "Permissoes" tab with the full permission matrix and inline CRUD via AJAX. However, the JS in `views/configuracoes/permissoes.php` still calls `/permissoes/store`, `/permissoes/edit/`, `/permissoes/update/`, `/permissoes/delete/` -- meaning the standalone `/permissoes` routes are actively used by the permissoes view. **The issue is architectural:** permissions exist in TWO places -- as a tab inside `/configuracoes` AND as a standalone `/permissoes` page. The sidebar only links to `/configuracoes/permissoes` (which goes to ConfiguracoesController, not PermissaoController). | **Refactor: Consolidate** | **HIGH** |
| 2 | `/admin/` and `/admin/dashboard` | Both routes map to the same `AdminController@index`. `/admin/dashboard` is redundant. | **Remove `/admin/dashboard` route** | **LOW** |
| 3 | `/ai/` and `/ai/dashboard` | Both routes map to the same `AIController@dashboard`. `/ai/dashboard` is redundant. | **Remove `/ai/dashboard` route** | **LOW** |
| 4 | `/auth/logout` (GET + POST) | Both GET and POST methods map to the same logout action. GET logout is a security risk (CSRF). | **Remove GET logout, keep POST only** | **MEDIUM** |

### 2.5 Controllers with No Route (All controllers are routed)

All 10 controllers have at least one route pointing to them. No orphan controllers found.

---

## 3. Specific Findings

### 3.1 Old /permissoes Route -- DEPRECATED Architecture

**Status:** The `/permissoes/*` route group (7 routes) is in a half-deprecated state.

**Details:**
- The sidebar link points to `/configuracoes/permissoes` which routes to `ConfiguracoesController@index` (the settings page with permissions tab)
- The `views/configuracoes/permissoes.php` view's JavaScript calls `/permissoes/store`, `/permissoes/edit/{id}`, `/permissoes/update/{id}`, `/permissoes/delete/{id}`, `/permissoes/update-roles`
- So the `/permissoes` API routes ARE actively used, but the page is never directly navigated to
- `PermissaoController::create()` and `PermissaoController::edit()` reference views that **do not exist** (`permissao_create.php`, `permissao_edit.php`)

**Recommendation:**
- Keep the `/permissoes/*` routes for the AJAX API calls (store, update, delete, update-roles)
- Remove the routes that render pages (`/permissoes/create`, `/permissoes/edit/{id}`, `/permissoes/` as page view) since the UI is now embedded in `/configuracoes` as a tab
- Alternatively, rename the group to `/api/permissoes` to clarify it's API-only
- **Priority: HIGH**

### 3.2 AI System Routes/Views

**Status:** The AI system has 20 routes (7 web + 13 API) and 4 views.

**Routes:**
- Web: `/ai/`, `/ai/dashboard`, `/ai/agents`, `/ai/agent/{id}`, `/ai/learning`, `/ai/execute`, `/ai/orchestrate`
- API: `/api/ai/*` (13 endpoints, no auth middleware)

**Views:**
- `views/ai/dashboard.php` -- used by `AIController::dashboard()`
- `views/ai/agents.php` -- used by `AIController::agents()`
- `views/ai/agent-detail.php` -- used by `AIController::agentDetail()`
- `views/ai/learning.php` -- used by `AIController::learning()`

**Analysis:**
- All AI views ARE referenced by their controller
- All AI routes ARE valid and point to existing methods
- **However:** The AI API routes have NO authentication middleware -- they are publicly accessible. This is a security concern.
- The sidebar does NOT include a link to the AI system -- users can only access it by typing the URL directly
- `AIService.php` exists at `src/Service/AIService.php` and is actively used by `AIController`

**Recommendation:**
- **Add AuthMiddleware to `/api/ai/*` routes** or at minimum verify which endpoints should be public
- **Add AI link to sidebar** if the feature is meant to be accessible, or remove the routes if it's not production-ready
- **Priority: MEDIUM** (security concern for public API endpoints)

### 3.3 Test Routes

**Status:** 5 test routes exposed without authentication:

| Route | Method |
|-------|--------|
| `/test` | HomeController@test |
| `/test/crud/users` | TestController@testUserCrud |
| `/test/crud/clientes` | TestController@testClienteCrud |
| `/test/crud/empresa` | TestController@testEmpresaCrud (method does not exist!) |
| `/test/database` | TestController@testDatabase (method does not exist!) |

**Critical Issues:**
- `TestController` only has 2 methods defined: `testUserCrud()` and `testClienteCrud()`
- Routes `/test/crud/empresa` and `/test/database` reference methods that **DO NOT EXIST** -- this will cause runtime errors
- The `TestController` file is 344 lines long (13.5 KB), but the last method ends at line 343. Methods `testEmpresaCrud()` and `testDatabase()` are missing.
- All test routes have NO authentication -- anyone can access them
- `test_classes.php` in the root is a development testing script

**Recommendation:**
- **Remove ALL `/test/*` routes in production**
- Remove `TestController.php` entirely OR wrap in `APP_ENV === 'local'` check
- Remove `test_classes.php` from production
- **Priority: HIGH** (security + broken routes)

### 3.4 admin/index.php View

**Status:** Used by `AdminController@index()` which is routed from `/admin/` and `/admin/dashboard`.

**Analysis:**
- The view follows the correct layout pattern (header.php -> content -> footer.php)
- It has hardcoded stats ("3 Usuarios", "Online", "v1.0") that are not dynamic
- The route `/admin/` is protected by AuthMiddleware
- However, the sidebar links to `/dashboard/administrador` NOT `/admin/`
- The `/admin/` route is effectively unreachable through normal navigation
- It appears to be a legacy admin page from the original "NovoFramework" boilerplate

**Recommendation:**
- **Remove** `AdminController.php`, `/admin/*` routes, and `views/admin/index.php`
- The `DashboardController` with role-specific dashboards (`/dashboard/administrador`, `/dashboard/produtor`, `/dashboard/estoquista`) has replaced this
- **Priority: HIGH** (dead code)

### 3.5 HomeController::index() - Orphaned Method

**Status:** `HomeController::index()` renders `views/home/index.php` but **no route calls this method**.

**Details:**
- Route `/` uses an anonymous function that redirects to `/auth/login` or `/dashboard/*`
- Route `/test` calls `HomeController::test()` (different method)
- The `index()` method and its view are completely orphaned
- `views/home/index.php` also has the broken layout pattern (duplicates topbar/sidebar/main wrappers)

**Recommendation:**
- Remove `HomeController::index()` method
- Remove `views/home/index.php`
- Keep only `HomeController::test()` if `/test` route is needed during development
- **Priority: HIGH**

---

## 4. Dead Code in Controllers

### 4.1 PermissaoController - Missing Views

| Method | Line | Missing View | Status |
|--------|------|-------------|--------|
| `create()` | 59-68 | `views/configuracoes/permissao_create.php` | View does not exist |
| `edit($id)` | 95-112 | `views/configuracoes/permissao_edit.php` | View does not exist |

These methods will throw fatal errors if called.

### 4.2 TestController - Missing Methods

| Route | Referenced Method | Exists? |
|-------|------------------|---------|
| `/test/crud/empresa` | `TestController::testEmpresaCrud()` | NO |
| `/test/database` | `TestController::testDatabase()` | NO |

### 4.3 ClienteController - Orphaned Method

| Method | Line | Route? | Status |
|--------|------|--------|--------|
| `show($id)` | 165-182 | No route registered | Orphaned - calls missing view |

### 4.4 Unused Imports

| File | Import | Used? |
|------|--------|-------|
| `TestController.php` | `use App\Core\Csrf;` | NOT USED (no CSRF validation in test methods) |
| `TestController.php` | `use App\Core\Debug;` | Used (Debug::log calls) |
| `TestController.php` | `use App\Core\Env;` | NOT USED |

---

## 5. RBAC Pattern Compliance

### 5.1 Views Without RBAC Checks

| View | Has RBAC? | Issue |
|------|-----------|-------|
| `views/admin/index.php` | Controller checks AuthMiddleware only | No role-specific check, just authenticated |
| `views/home/index.php` | No RBAC at all | Orphaned view anyway |
| `views/ai/dashboard.php` | No RBAC check | No sidebar link exists |
| `views/ai/agents.php` | No RBAC check | No sidebar link exists |
| `views/ai/learning.php` | No RBAC check | No sidebar link exists |
| `views/ai/agent-detail.php` | No RBAC check | No sidebar link exists |

### 5.2 Views With Proper RBAC

| View | RBAC Implementation |
|------|-------------------|
| `views/cliente/index.php` | Controller checks `Rbac::check('clientes.listar')` |
| `views/cliente/create.php` | Controller checks `Rbac::check('clientes.criar')` |
| `views/cliente/edit.php` | Controller checks `Rbac::check('clientes.editar')` |
| `views/user/index.php` | Controller checks `Rbac::isAdmin()` |
| `views/configuracoes/index.php` | Controller checks `Rbac::isAdmin()` for save |
| `views/dashboard/*.php` | Controller checks role-specific permissions |

---

## 6. Summary Table

| # | Item | Type | Location | Recommendation | Priority |
|---|------|------|----------|----------------|----------|
| 1 | `views/home/index.php` | Orphan View | `views/home/index.php` | **REMOVE** - No route, broken layout pattern | HIGH |
| 2 | `HomeController::index()` | Dead Method | `src/Controllers/HomeController.php:12-27` | **REMOVE** - Not called by any route | HIGH |
| 3 | `views/admin/index.php` | Legacy View | `views/admin/index.php` | **REMOVE** - Replaced by dashboard system | HIGH |
| 4 | `AdminController.php` | Dead Controller | `src/Controllers/AdminController.php` | **REMOVE** - Replaced by DashboardController | HIGH |
| 5 | `/admin/*` routes | Dead Routes | `public/index.php:35-38` | **REMOVE** - No navigation links | HIGH |
| 6 | `/test/*` routes | Test Routes | `public/index.php:10-16` | **REMOVE** in production | HIGH |
| 7 | `TestController.php` | Test Controller | `src/Controllers/TestController.php` | **REMOVE** in production | HIGH |
| 8 | `test_classes.php` | Test Script | `/var/www/html/sisloc/test_classes.php` | **REMOVE** from repo or add to .gitignore | HIGH |
| 9 | Missing views for PermissaoController | Missing Files | `views/configuracoes/permissao_create.php`, `permissao_edit.php` | **CREATE** or remove the controller methods | HIGH |
| 10 | Missing view `cliente/show.php` | Missing File | `views/cliente/show.php` | **CREATE** or remove show() method + route | HIGH |
| 11 | `/test/crud/empresa` route | Broken Route | `public/index.php:15` | **REMOVE** - Method doesn't exist | HIGH |
| 12 | `/test/database` route | Broken Route | `public/index.php:16` | **REMOVE** - Method doesn't exist | HIGH |
| 13 | `/permissoes/*` group | Redundant Routes | `public/index.php:71-79` | **CONSOLIDATE** into `/configuracoes` or rename to `/api/permissoes` | HIGH |
| 14 | `/admin/dashboard` route | Duplicate Route | `public/index.php:37` | **REMOVE** - Same as `/admin/` | LOW |
| 15 | `/ai/dashboard` route | Duplicate Route | `public/index.php:96` | **REMOVE** - Same as `/ai/` | LOW |
| 16 | GET `/auth/logout` | Security Issue | `public/index.php:30` | **REMOVE** - Keep POST only | MEDIUM |
| 17 | `/api/ai/*` no auth | Security Issue | `public/index.php:107-132` | **ADD AuthMiddleware** or audit which should be public | MEDIUM |
| 18 | AI system no sidebar link | UX Issue | `views/layout/sidebar.php` | **ADD link** if feature is active, or hide if not | MEDIUM |
| 19 | `ClienteController::show()` | Orphaned Method | `src/Controllers/ClienteController.php:165-182` | **REMOVE** or add route + create view | MEDIUM |
| 20 | Unused imports in TestController | Dead Code | `src/Controllers/TestController.php:8-9` | **REMOVE** unused `use` statements | LOW |

---

## 7. Recommended Cleanup Actions (in order)

### Phase 1: Critical (Remove broken code)
1. Remove `/test/crud/empresa` and `/test/database` routes (broken -- methods don't exist)
2. Create `views/cliente/show.php` OR remove `ClienteController::show()` method
3. Create `views/configuracoes/permissao_create.php` and `permissao_edit.php` OR remove those PermissaoController methods

### Phase 2: Security (Protect production)
4. Wrap or remove ALL `/test/*` routes for production
5. Remove `test_classes.php` or add to `.gitignore`
6. Add AuthMiddleware to `/api/ai/*` routes (or verify which should be public)
7. Remove GET `/auth/logout` route (keep POST only)

### Phase 3: Cleanup (Remove dead code)
8. Remove `HomeController::index()` and `views/home/index.php`
9. Remove `AdminController.php`, `/admin/*` routes, and `views/admin/index.php`
10. Consolidate `/permissoes/*` -- either merge into `/configuracoes` or rename to `/api/permissoes`
11. Remove duplicate routes: `/admin/dashboard`, `/ai/dashboard`

### Phase 4: Improve (Optional enhancements)
12. Add AI system link to sidebar (if feature is production-ready)
13. Remove `ClienteController::show()` if not needed
14. Clean up unused imports in TestController

---

## 8. Files That Should Be Added to .gitignore

```
test_classes.php
```

---

## 9. Architecture Note

The permission system has evolved from a standalone CRUD page (`/permissoes`) to an embedded tab inside the settings page (`/configuracoes` -> "Permissoes" tab). The current state is a hybrid:
- The **UI** lives in `/configuracoes` (tab-based)
- The **API** for permission operations still lives at `/permissoes/*`
- The sidebar links to `/configuracoes/permissoes` (settings page)

This is workable but confusing. The recommended end-state is to either:
- (a) Move all permission API routes under `/configuracoes/api/permissoes/*`
- (b) Or keep `/permissoes/*` as API-only and rename the route group to `/api/permissoes/*`
