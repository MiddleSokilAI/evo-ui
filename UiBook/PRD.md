# evoUI PRD

Status: `phase-1 skeleton in progress`
Date: `2026-05-03`
Repository: `/Users/dmi3yy/PhpstormProjects/Extras/evoUI`
Layer: `Evolution CMS UI foundation`

## 0. Naming Contract

- Product/package identity: `evo-ui`
- Composer dev package: `middleduck/evo-ui`
- PHP namespace: `EvoUI`
- Blade component namespace: `x-evo::`
- Blade view namespace: `evo::`
- JavaScript namespace: `evo-ui`
- Browser global, when needed: `window.EvoUI`
- Central Livewire JS bridge: `EvoLivewireBridge`
- Central dirty-state bridge: `DirtyStateBridge`
- Extension facade/service: `EvoUI`

## 1. Purpose

`evoUI` is the base UI foundation for future Evolution CMS manager modules and, later, a possible new manager layer. It must provide an Evo-native Blade/Livewire component layer powered by DaisyUI themes, with minimal scoped JavaScript and a clean package contract that future modules can reuse.

Phase 1 has started after the initial audit: the package skeleton, clean routing module shell, Livewire 4 bridge, local assets, theme sync, and base layout primitive are now the implementation baseline. This PRD remains the contract for keeping the code small, modern, and scalable.

Current implementation target: **Evolution CMS 3.5.6+ only**.

## 2. Repo Audit Sources

Before writing this PRD, the following sources were checked. Architectural decisions below are grounded in these files and patterns.

### MiddleDuck / EvoBook

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/0.md` | Host-first rule: keep reusable workers, skills, and package foundations clean before scaling. |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/DuckBook/DUCKBOOK.md` | Control-plane role and shelf rules. |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/EvoBook/EVOBOOK.md` | Evolution CMS atlas entrypoints, manager/runtime ownership, testing mindset. |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/EvoBook/Extras/entries/sarticles.md` | Current sArticles package contract, demo contract, theme and manager UI notes. |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/EvoBook/Extras/entries/sarticles-technical-report.md` | Detailed sArticles structure, risks, ajax/table/theme findings. |
| `/Users/dmi3yy/PhpstormProjects/MiddleDuck/EvoBook/Extras/entries/sarticles-developer-report.uk.md` | Ukrainian developer handoff for sArticles modernization and publish rules. |

### Target package

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/evoUI` | Target repo. Current state is minimal: `.git`, `.idea`, `LICENSE`; no package skeleton yet. |

### sArticles reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/composer.json` | Existing Evolution module composer type, provider discovery, PHP/Evo package shape. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/src/sArticlesServiceProvider.php` | Manager-only boot, routes, views, translations, module registration, publish map. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/module/sArticlesModule.php` | Legacy manager dispatcher risk and tab routing model. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/views/index.blade.php` | Manager shell, current legacy CDN/jQuery dependencies to avoid in evoUI, and the first hand-off point to `x-evo::module-tabs`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/views/articlesTab.blade.php` | Table with search, filters, ajax pagination, row actions, URL state, loading state. |
| `/Users/dmi3yy/PhpstormProjects/Extras/evoUI/resources/css/evo-ui.css` | Daisy theme tokens for `evolight`, `evolightness`, `evodark`, `evodarkness`, shared table primitives, module tabs, and Windows-only scrollbar styling. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/assets/js/admin.js` | Ajax navigation, delete confirmations, and legacy adapters; theme/platform logic belongs to `evoUI`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/src/Controllers/sArticlesController.php` | Existing settings persistence into `custom/config/seiger/settings/sArticles.php`; keep the publishable settings idea, but replace manual string building with a shared writer. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sArticles/module/sArticlesModule.php` | Legacy request-to-config settings dispatcher; useful as a warning against handwritten save routers. |

### sSeo / sCommerce settings references

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/sSeo/src/sSeoServiceProvider.php` | Publishable config contract: package defaults publish into `custom/config/seiger/settings/...`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sSeo/src/Controllers/sSeoController.php` | Safer config persistence: load current file, merge updates, preserve order, check writability, clear cache. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sCommerce/src/sCommerceServiceProvider.php` | Larger module provider pattern and settings publish contract. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sCommerce/views/settingsTab.blade.php` | Repeated settings fields and constructor config breadth; use as scope reference, not markup to copy. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sCommerce/views/partials/textField.blade.php` | Label/help/hint/value field contract. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sCommerce/views/partials/selectField.blade.php` | Select options as declarative data. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sCommerce/views/partials/checkboxField.blade.php` | Boolean setting pattern; evoUI must implement it without legacy hidden input/changestate helpers. |

### Multifields reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/plugins/multifields.plugin.php` | Legacy event map: manager header assets, request actions, `OnDocFormSave`, `OnDocFormDelete`; useful as a list of integration points to replace safely. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Base/Core.php` | Config/data resolver: TV id/name config files, JSON value/file storage, template normalization, save/delete behavior. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Base/Elements.php` | Dynamic element registry, nested item rendering, action list, attribute/data mapping, fallback to Evo TV renderers. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Multifields/Multifields.php` | Root repeater toolbar, template picker, import/export/fullscreen, breakpoint/grid state. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Row/Row.php` | Nested row/group pattern, child templates, add/move/delete/resize actions, column metadata. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Table/Table.php` | Repeater table pattern: configurable column types, add/delete columns/rows, cell type switching. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Thumb/Thumb.php` | Media-card field pattern and linked image/thumb behavior. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Image/Image.php` | File-browser bridge needs: single/multi image select, linked thumb update. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Elements/Richtext/Richtext.php` | Editor lifecycle risk: iframe/editor init, theme mode mapping, `wire:ignore` requirement for EvoUI. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/src/Base/Front.php` | Frontend JSON rendering contract and TV/file data compatibility for migration. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/assets/plugins/multifields/config/example.slider.php` | Nested template config with row, thumb, image, richtext, text fields and hidden child templates. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/assets/plugins/multifields/config/example.table.php` | Configurable table/repeater schema, column/cell type metadata, frontend template transforms. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/assets/plugins/multifields/config/example.thumb.php` | Media repeater and transformer pattern, including phpthumb-style prepared values. |
| `/Users/dmi3yy/PhpstormProjects/Extras/multifields-master/assets/plugins/multifields/config/example.toolbar.php` | Toolbar/breakpoint config idea; schema is inconsistent in legacy and must be normalized in evoUI. |

### TemplatesEdit reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/plugin.templatesedit.php` | Legacy event map: `OnDocFormTemplateRender`, `OnDocFormRender`, `OnDocFormSave`, template builder render/save/delete. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/class/templatesedit.class.php` | Template/role-aware resource form layout resolver, default fields, TV categories, field rendering, custom field save. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/class/templateseditbuilder.class.php` | Visual builder contract: tabs, columns, fields, categories, role-specific JSON configs, import/export, default/base config. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/configs/fields.php` | Canonical Evo resource field catalog with labels/help. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/configs/template__default.php` | Default layout split into General, Content, SEO, Settings tabs. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/configs/template_default.php` | Field-level UI settings: size, label position, rows, classes; useful as schema input, not as CSS to copy. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/configs/custom_fields.example.php` | Custom field save/prepare contract; evoUI should replace arbitrary closures/snippets in editable config with registered transformers. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/js/TemplatesEditBuilder.js` | Builder UX reference: drag/drop tabs/columns/fields/categories, resize 12-col layout, field settings panel, import/export. |
| `/Users/dmi3yy/PhpstormProjects/Extras/templatesedit3-3.1.x/assets/plugins/templatesedit/tpl/*.tpl.php` | Legacy markup decomposition; evoUI should replace with Blade components and Daisy/Evo tokens. |

### sLang multilingual reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/composer.json` | Package contract: Evolution module, Laravel provider, multilingual management for resources/modules. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/database/migrations/2022_10_15_162000_create_s_lang_tables.php` | Storage model: base content stays in Evo tables; translated resource fields go to `s_lang_content`; translated TVs go to `s_lang_tmplvar_contentvalues`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/src/sLang.php` | Config keys: `s_lang_default`, `s_lang_config`, `s_lang_front`, `s_lang_tvs`; content field list and TV language helpers. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/src/Controllers/sLangController.php` | Existing save/read behavior for translated resource fields and translated TV values. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/src/Models/sLangContent.php` | Query and model behavior for translated content records. |
| `/Users/dmi3yy/PhpstormProjects/Extras/sLang/src/Models/sLangTmplvarContentvalue.php` | Translated TV value model shape. |

### MaryUI reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/composer.json` | Mary package dependencies and provider model. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/config/mary.php` | Configurable component prefix and route prefix idea. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/MaryServiceProvider.php` | Component registration, aliases, Blade directives, package boot pattern. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Button.php` | Declarative component props, icon, spinner, `wire:loading` DX. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Input.php` | Field wrapper, validation, `wire:model`, slots, minimal Alpine. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Table.php` | Declarative table headers/rows, row/cell decoration, sorting, pagination, slots, `wire:key`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Tabs.php` | Split tab label/content registration, `wire:model` entangle, Daisy tab classes. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Tab.php` | Tab label registration and panel rendering pattern. |
| `/Users/dmi3yy/PhpstormProjects/Extras/mary/src/View/Components/Modal.php` | Daisy `dialog` modal with Livewire/Alpine binding. |

### Mary paper demo

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/css/app.css` | Tailwind 4 + DaisyUI 5 source scanning and theme plugin pattern. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/composer.json` | Paper demo versions: Laravel 12, Livewire `^3.6`, Volt `^1.7`, Mary `^2.1`; useful as DX reference only, not as evoUI dependency baseline. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/package.json` | Tailwind 4, DaisyUI 5, Vite demo build; useful for source/theme ideas only because evoUI must use Evo's package asset flow, not Vite. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/posts/index.blade.php` | Livewire-first DX with URL-bound search/filter/sort and clean component use. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/profile.blade.php` | `x-tabs`/`x-tab` with `wire:model.live`, URL-bound active tab, and clean tab panel composition. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/posts/create.blade.php` | Form contract: `#[Rule]`, `x-form wire:submit`, field components, action slot, spinner submit. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/posts/edit.blade.php` | Edit form contract: model fill in `mount`, validation attributes, server action, redirect/toast pattern. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/comments/index.blade.php` | Nested Livewire child components with stable `wire:key`, lazy placeholder, child-to-parent events. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/livewire/comments/card.blade.php` | Row/card action menu, inline edit form, `wire:loading.class`, `wire:target`, and local Alpine UI state. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/resources/views/components/layouts/app.blade.php` | Laravel layout reference; useful for component composition, not for the asset pipeline. |
| `/Users/dmi3yy/PhpstormProjects/Extras/paper.mary-ui.com/routes/web.php` | Volt route pattern as a reference only; evoUI on Livewire 4 should stay class-based/module-routed for Evo 3.5. |

### Livewire / Evolution integration

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/composer.json` | Livewire 4 dependency baseline. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/config/livewire.php` | Livewire 4 config needs: component locations, layout, assets, navigate, morph markers, payload guards. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/src/LivewireServiceProvider.php` | Livewire 4 service registration, component discovery, mechanisms/features. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php` | Script route, CSRF token, update URI, asset URL behavior. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/src/Mechanisms/HandleRequests/HandleRequests.php` | Update route, `web` middleware requirement, Livewire header guard, trim/null middleware shims. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/components.md` | Livewire component rendering, props, and class-based component rules. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/nesting.md` | Prefer Blade components unless the child needs independent Livewire state; stable keys for loops. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/attribute-url.md` | URL-bound search/filter/sort/page state. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/wire-model.md` | Livewire 4 `wire:model.live`, default debounce, and explicit debounce modifiers for search controls. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/javascript.md` | Livewire 4 lifecycle hooks: `component.init`, `morph.updated`, and central JS hook constraints. |
| `/Users/dmi3yy/PhpstormProjects/Extras/livewire/docs/upgrading.md` | Livewire 4 interceptor direction: prefer `interceptMessage`/`interceptRequest` over deprecated request/commit hooks. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/composer.json` | Evolution 5.0.1 dependency baseline: PHP `^8.4`, Illuminate `12.*`, current `livewire/livewire:^3.0`, Mary, Tabler icons. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/Providers/LivewireManagerServiceProvider.php` | Existing Evo manager Livewire bridge: `/manager/livewire/update`, `/manager/livewire/livewire.js`, `mgr` middleware, foundation shims. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/Livewire/Foundation/*` | Shim classes for missing `Illuminate\Foundation` middleware/events/auth traits. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/Providers/BladeServiceProvider.php` | Evo Blade directives and `mary-icon` registration. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/View/Components/MaryIcon.php` | Mary-to-Tabler icon adapter pattern. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/ManagerTheme.php` | Manager theme mode resolution: `lightness`, `light`, `dark`, `darkness`. |
| `/Users/dmi3yy/PhpstormProjects/Extras/5.0.x/core/src/Controllers/Frame.php` | Manager body class theme mode behavior. |

### Declarative table reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/table/FilteredTable.vue` | Rich table shell: header slots, search, filters, column settings, loading, empty state, row events. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/configs/tables/**` | Declarative columns/filters/actions model: `key`, `dataKey`, `title`, `width`, `sortable`, `hidden`, `cellRenderer`, `formField`, filters. |
| User-provided Vue layout component in this task | Default resizable layout contract: horizontal/vertical panes, persisted sizes, nested layout keys, pane metadata, slider behavior. |

### Declarative form reference

| Source | Used for |
| --- | --- |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/FormWithFG.vue` | Form shell split into header actions, body, tabs, generated layout, save/reset lifecycle, and dirty/disabled states. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/form/dynamic-form/FLayout.vue` | Layout layer that renders config cards and keeps wizard/columns outside field components. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/form/dynamic-form/FLayoutCard.vue` | Card contract: label, rights, span, slot override, field list, and nested field renderer. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/form/dynamic-form/FFieldWrapper.vue` | Field wrapper contract: label, help/extra label, validation state, input renderer, and post-field hints. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/components/form/form-generator/GFormRow.vue` | Config editor pattern for changing field visibility, readonly/required flags, span and position. |
| `/Users/dmi3yy/PhpstormProjects/CareOfficeWeb/front/src/configs/forms/fg/journals/work-request.ts` | Rich form config vocabulary. evoUI borrows only generic concepts: field type, label, span, required, disabled, list/options, grouping. |

### External current references

| Source | Used for |
| --- | --- |
| `https://packagist.org/packages/livewire/livewire` | Current Livewire package versions and real required Composer dependencies. |
| `https://livewire.laravel.com/docs/4.x/quickstart` | Livewire 4 layout requirements, `@livewireStyles`, `@livewireScripts`, one-root component rule, `wire:model`, `wire:submit`. |
| `https://livewire.laravel.com/docs/4.x/upgrading` | Livewire 4 request/message interceptors and performance changes. |
| `https://gist.github.com/Dmi3yy/48b153d5ab60e2524e81170d38bf4bcd` | First demo table domain: `SiteContent`, TVs, closure tree, filters/sorting examples. |

## 3. Product Scope

### Goal

Create a base Evolution CMS module/package named `evoUI` that provides:

- a working Livewire bridge inside Evolution CMS Manager;
- Evo-native Blade components with namespace like `x-evo::button`;
- DaisyUI-based manager themes;
- minimal JavaScript helpers that replace legacy jQuery/select2/jquery-ui/bootstrap patterns;
- a demo Evolution CMS runtime under a git-ignored `demo/`;
- a first demo tab showing `site_content` via a Livewire table.

### Non-goals for MVP

- Do not copy MaryUI internals.
- Do not require full `laravel/framework` in Evolution modules.
- Do not build a complete manager replacement yet.
- Do not migrate sArticles to evoUI in this first pass.
- Do not use CDN dependencies for manager UI.
- Do not depend on jQuery, Select2, jQuery UI, Bootstrap modal, or global manager selectors for new components.

## 4. Positioning

`evoUI` is inspired by MaryUI and sArticles, but it is not a wrapper around MaryUI.

MaryUI is the reference for:

- clean Blade component API;
- Livewire-first developer experience;
- DaisyUI as the CSS foundation;
- small component classes with declarative props and slots;
- limited JavaScript and Alpine used only when it pays for itself.

sArticles is the reference for:

- real Evolution Manager constraints;
- ajax navigation and URL state;
- search/filter/table behavior;
- tabbar and action button ergonomics;
- Evolution theme synchronization;
- package publish contract and demo runtime pattern.

The final product must be Evo-native:

- namespace: `x-evo::*`;
- package identity: Evolution CMS module/foundation;
- manager theme bridge: Evolution Manager first, DaisyUI second;
- assets: published from package source;
- compatibility: Manager-safe and module-safe;
- architecture: reusable by future Evolution modules.

## 5. Target Architecture

### System Layers

1. Core package setup
2. Livewire bridge
3. EvoUI Blade components
4. DaisyUI theme layer
5. Manager theme bridge
6. Asset pipeline
7. JavaScript layer
8. Alpine/vanilla helpers
9. Evolution globals bridge
10. Documentation/examples layer
11. Demo runtime layer
12. Clean routing module shell

### Clean Routing Module Contract

evoUI must run as a self-owned iframe document. The normal menu entry should use Evolution routing modules (`manager/modules/{module-id}`), not the legacy `index.php?a=112&id=...` execute-module path.

Rules:

- The module page must not extend `manager::template.page`.
- The module page must not load manager `styles.min.css`, Bootstrap, jQuery, `main.js`, `tabpane.js`, Roboto, or manager font CSS.
- The module owns its HTML document, CSS, JS, Livewire assets, theme sync, and component shell.
- Parent manager frame may keep legacy assets until the manager itself is rewritten, but the evoUI iframe document must stay clean.

Current phase-1 decision:

- Use `registerRoutingModule()` directly; Evo 3.5.6+ is the required host.
- Do not make the new module depend on the old execute-module page shell.
- Direct unauthenticated access to a routing module should be handled by the Evolution manager login flow; normal QA starts from `/manager/` and opens the module through the menu.

### Modern Laravel-Style Contract

evoUI should follow current Laravel 12/13-style code patterns where they help structure and reduce code, without requiring `laravel/framework`.

Rules:

- Prefer PHP attributes for Livewire action authorization, for example `#[Authorize('exec_module')]`.
- Map authorization through a package gate bridge, not through ad hoc checks inside views.
- Include small compatibility shims only for Laravel foundation classes that Livewire actually touches.
- Do not add broad legacy compatibility layers to keep old manager UI patterns alive.
- Do not pull `illuminate/auth` or the full Laravel foundation only to satisfy one missing trait/class.
- Important small bridges are allowed when they close a real runtime gap; anything broad or speculative stays out until needed.

### Proposed Package Structure

```text
evoUI/
  UiBook/
    PRD.md
    COMPONENTS.md
    THEMES.md
    LIVEWIRE-BRIDGE.md
    ROADMAP.md
  composer.json
  evo-ui.config.php
  config/
    evo-ui.php
    livewire.php
  src/
    evoUIServiceProvider.php
    Support/
      Assets.php
      ManagerTheme.php
      EvoUrl.php
    Livewire/
      Bridge/
        LivewireManagerBridge.php
      Components/
        Table.php
    View/
      Components/
        Button.php
        Input.php
        Textarea.php
        Select.php
        Checkbox.php
        Badge.php
        Alert.php
        Card.php
        Table.php
        Pagination.php
        Modal.php
        Dropdown.php
        Tabs.php
        Toolbar.php
  routes/
    module.php
  resources/
    views/
      layouts/
        manager.blade.php
      components/
        button.blade.php
        input.blade.php
        table.blade.php
      livewire/
        table.blade.php
      demo/
        index.blade.php
    js/
      evo-ui.js
      components/
        select.js
        sortable.js
        modal.js
        dropdown.js
        tabs.js
        tree.js
        editor-adapter.js
    css/
      evo-ui.css
  demo/
    .gitignored Evolution CMS 3.5.6+ runtime
```

## 6. Composer and Dependency Strategy

### Current facts

Livewire 4 current package requirements from Packagist:

- `php:^8.1`
- `illuminate/database:^10|^11|^12|^13`
- `illuminate/routing:^10|^11|^12|^13`
- `illuminate/support:^10|^11|^12|^13`
- `illuminate/validation:^10|^11|^12|^13`
- `laravel/prompts:^0.1.24|^0.2|^0.3`
- `league/mime-type-detection:^1.9`
- `symfony/console:^6|^7|^8`
- `symfony/http-kernel:^6.2|^7|^8`

Evolution 3.5.6+ target assumptions:

- PHP `^8.3`
- Evolution CMS `>=3.5.6 <4.0`
- Livewire `^4.0` installed by this package
- Blade Icons/Tabler are provided by the Evo runtime/demo stack

### Decision

For evoUI source package:

- require Evolution CMS `>=3.5.6 <4.0`, not Laravel framework;
- use Livewire 4 as the only Livewire baseline;
- keep the Livewire bridge small and module-owned for Evo 3.5.6+;
- do not require `laravel/framework`;
- do not require MaryUI;
- use the Blade Icons/Tabler stack already provided by Evolution instead of maintaining local SVG maps;
- do not require Select2, jQuery UI, Bootstrap JS, or jQuery.

### Current Runtime Decision

The demo runtime is installed from Evolution CMS `3.5.6+`, and the package is wired with `livewire/livewire:^4.0` only. No dual Livewire baseline is supported.

Rules:

- Treat Livewire 4 as the foundation target.
- Do not introduce dual Livewire 3/4 component APIs in evoUI.

## 7. Livewire Bridge

### Objective

Run Livewire components inside Evolution CMS Manager without pulling full Laravel and without breaking manager middleware, CSRF, sessions, routes, assets, or Blade rendering.

### Reference pattern

evoUI owns the Evo 3.5.6+ module-level Livewire bridge. It should stay small and direct.

### evoUI bridge requirements

- Use manager-scoped routes:
  - `POST /manager/livewire/update`
  - `GET /manager/livewire/livewire.js`
- Use `mgr` middleware and preserve current manager session/auth behavior.
- Preserve CSRF/session behavior; if Evo's `web` group is required by Livewire 4, the bridge must verify that adding it does not bypass manager auth.
- Register evoUI Livewire components explicitly.
- Keep config minimal: component layout, module tabs, asset path, and theme map.
- Provide foundation shims only for the classes Livewire 4 actually touches in the Evo 3.5 module runtime.
- Load required early shims through Composer `autoload.files` when Livewire needs a missing Foundation trait/class before service providers boot.
- Bind a minimal Gate contract bridge so Livewire `#[Authorize]` can map to Evolution manager permissions.
- Never globally override app routes outside the manager-scoped prefix.

### Authorization bridge

The first authorization layer is `EvoUI\Auth\EvoGate`.

Rules:

- `#[Authorize('exec_module')]` maps to `evo()->hasPermission('exec_module', 'mgr')`.
- Role-style ability names may use `role:{id}` for manager role checks.
- Components may define package-local abilities through the gate bridge when needed.
- Every Livewire action that mutates data must authorize on the server.
- Blade can hide buttons for convenience, but Blade visibility is never the source of truth.
- Client state is never trusted for permissions.

Foundation shim rules:

- `Illuminate\Foundation\Auth\Access\AuthorizesRequests` is provided only when missing.
- `Illuminate\Auth\Access\AuthorizationException` is provided only when missing.
- Foundation HTTP middleware/event shims are provided only when Livewire touches them and the host does not.
- Livewire may touch Laravel's asset-tag facade during boot; evoUI may provide a tiny asset-attributes shim for that API, but this is not a package build pipeline.
- When a future Evo host provides real Laravel classes, the package must defer to the host.

### Livewire usage rules

- Components must have a single root element.
- Use `wire:key` for rows, modals, tabs, nested components, sortable items, file/editor wrappers.
- Use `wire:ignore` around editor/file-picker widgets with their own lifecycle.
- Use `wire:loading`, `wire:target`, and skeleton/opacity states for table operations.
- Prefer URL-bound state for search/filter/page/sort where manager reloadability matters.
- Avoid `wire:navigate` for first MVP unless tested inside manager iframe/history.

### Livewire lifecycle integration

Livewire JavaScript lifecycle hooks are allowed only through the central `EvoLivewireBridge` adapter.

Required normalized events to emit from the adapter:

- `message.sent`
- `message.received`
- `message.failed`
- `element.updated`
- `component.initialized`

Rules:

- Components must not call `Livewire.hook(...)` directly.
- `EvoLivewireBridge` owns all hook registration.
- On Livewire 4, `EvoLivewireBridge` must use `Livewire.interceptMessage(...)` for message state and `Livewire.hook('component.init')` / `Livewire.hook('morph.updated')` for component and DOM re-init.
- Legacy hook names like `message.sent`, `message.received`, `message.failed`, `element.updated`, and `component.initialized` are EvoUI event names, not the direct Livewire 4 integration API.
- Hooks are only for:
  - re-initializing scoped helpers after morphs;
  - logging and dev diagnostics;
  - global loading indicators.
- Business logic must remain in Livewire/PHP actions or explicit component events, not hidden inside lifecycle hooks.
- The bridge targets Livewire 4 hooks only.

## 8. EvoUI Blade Components

### Namespace

Use namespaced anonymous/class components:

- `x-evo::button`
- `x-evo::input`
- `x-evo::textarea`
- `x-evo::select`
- `x-evo::checkbox`
- `x-evo::badge`
- `x-evo::alert`
- `x-evo::card`
- `x-evo::table`
- `x-evo::pagination`
- `x-evo::modal`
- `x-evo::dropdown`
- `x-evo::tabs`
- `x-evo::toolbar`
- future: `x-evo::sortable`, `x-evo::tree`, `x-evo::editor`, `x-evo::file-picker`

Correct Blade namespace standard:

```blade
<x-evo::button />
<x-evo::table />
<x-evo::modal />
```

Do not use custom Blade aliases as the public standard.

### Design principles

- Components are Evo-native wrappers around DaisyUI classes and Evo theme tokens.
- Public props are stable and documented.
- Slots support real manager workflows.
- Components work in plain Blade and inside Livewire.
- Components do not assume MaryUI internals.
- Components use scoped classes under `.evo-ui` plus Daisy utility classes.
- Icons use the existing Evolution/Tabler icon path when available.

### MaryUI / Livewire Patterns Adopted

The implementation should follow MaryUI as a pattern library, not as a dependency.

- Livewire class owns state, query building, actions, URL state, sorting, pagination, and permissions.
- Blade components own markup only: table shell, toolbar, filters, header cells, rows, cells, and pagination.
- The Livewire table view stays thin and composes `x-evo::table` instead of carrying raw table HTML.
- Table behavior is declarative through config: `columns`, `filters`, `actions`, `row_actions`, `order_by`, `search`, `per_page`, and `per_page_options`.
- Headers use a sortable contract like MaryUI: column key, sort field, current direction, and a single Livewire action.
- Rows use stable `wire:key` based on the row id, never loop index.
- Row selection for manager tables is single-select by default and drives toolbar actions.
- Tabs follow MaryUI's split-label pattern: `x-evo::tab` registers its label into `x-evo::tabs`, while panel content stays separate.
- Tables may expose `views` like `table` and `list`; `viewMode` is Livewire/URL state and the list renderer is driven by table config (`list.title`, `list.subtitle`, `list.meta`).
- List/card view must expose its own order control built from the same sortable column config as table headers.
- On mobile, top-level tab labels collapse to icons, search opens from an icon trigger, and filters collapse behind a filter trigger while preserving the same Livewire state.
- Prefer Blade components over nested Livewire components unless the child needs independent server state.
- Use `#[Url]` only for shareable table state: search, sort, direction, page, filters, view mode, and per-page size.
- `wire:model.live` is the default for reactive manager controls; add explicit debounce for text search and prefer action buttons for expensive filters.
- Use `wire:submit` for forms, `#[Rule]`/`validate()` for validation, and component action slots for submit/cancel buttons.
- Use `wire:loading` / `wire:target` for local loading feedback instead of custom global state inside Blade components.
- Use Livewire 4 class-based components for evoUI now; Paper's Volt examples are a DX reference, not a dependency or routing model.
- Empty states should be componentized and include a clear recovery action when filters/search produce no results.

Current table component decomposition:

```blade
<x-evo::table>
<x-evo::table.toolbar>
<x-evo::table.filter>
<x-evo::table.header-cell>
<x-evo::table.row>
<x-evo::table.cell>
<x-evo::table.pagination>
```

Standard filter primitives copied as concepts only:

- `select`
- `multi-select`
- `segmented`
- `toggle`
- `date-range`

Do not port CareOffice domain-specific filters, lists, rights rules, tree filters, or business renderers into EvoUI core.

### MVP components

| Component | MVP API |
| --- | --- |
| `Button` | `label`, `icon`, `iconRight`, `variant`, `size`, `href`, `type`, `spinner`, `tooltip`, `disabled` |
| `Input` | `label`, `name`, `value`, `placeholder`, `icon`, `clearable`, `error`, `hint`, `wire:model` passthrough |
| `Textarea` | `label`, `rows`, `error`, `hint`, `wire:model` passthrough |
| `Select` | `label`, `options`, `option-value`, `option-label`, `placeholder`, `multiple`, `searchable` via native EvoSelect, `wire:model` |
| `Checkbox` | `label`, `value`, `checked`, `toggle`, `wire:model` |
| `Badge` | `value`, `variant`, `size`, `icon` |
| `Alert` | `title`, `description`, `variant`, `icon`, `actions` slot |
| `Card` | `title`, `subtitle`, `actions`, `footer`, `compact` |
| `Table` | `columns`, `rows`, `row-key`, `sort`, `actions`, `empty`, `loading`, `selectable`, `link` |
| `Pagination` | Livewire paginator-compatible, Daisy styled, manager compact mode |
| `Modal` | `wire:model`, `id`, `title`, `actions`, `persistent`, focus behavior |
| `Dropdown` | `trigger`, `items`, `align`, `close-on-click`, keyboard close |
| `Tabs` | `items`, `active`, `wire:model`, `url-state`, horizontal overflow arrows |
| `Toolbar` | left/middle/right slots, compact manager actions |
| `Layout` | `vertical`, `horizontal`, `size`, `collapsed`, `secondCollapsed`, `static`, `auto`, `sliderDisabled`, `removeSlider`, `layoutKey`, `firstKey`, `secondKey`, `first` slot, `second` slot |

### Default layout component

`x-evo::layout` is the default composition primitive for richer manager UIs. It should reproduce the useful behavior of the provided Vue layout component in an Evo-native Blade/Livewire form.

Layout is a core primitive, not just another visual component.

Purpose:

- split a manager page into two panes;
- support horizontal and vertical orientation;
- support static pixel size or percentage size;
- support collapsed first pane and collapsed second pane;
- support optional resize slider;
- persist pane size in `localStorage`;
- support nested layouts without key collisions;
- expose pane metadata through `data-role`, `data-key`, `data-side`, and optional `data-content-component`.

Public API:

```blade
<x-evo::layout
    horizontal
    size="35"
    layout-key="site-content.editor"
>
    <x-slot:first>
        <livewire:evo-ui.table preset="site_content" />
    </x-slot:first>

    <x-slot:second>
        <livewire:evo-ui.site-content-preview />
    </x-slot:second>
</x-evo::layout>
```

Rules:

- layout does not depend on specific content;
- layout must be universal for tables, editors, trees, previews, forms, and mixed manager workspaces;
- layout works without Livewire;
- layout state is localStorage by default, with optional Livewire sync only when a module explicitly needs server-visible pane state;
- `horizontal` means left/right panes; `vertical` means top/bottom panes.
- Default orientation is `horizontal`.
- `size` controls the first pane; when `static` is true it is pixels, otherwise percent.
- `layoutKey` is required for reusable module layouts; if omitted, the component may derive one from route/component context but must avoid noisy keys like `index`, `home`, `undefined`, or `null`.
- Nested layouts inherit the parent pane key and append `.layout`.
- Slider drag uses scoped pointer/mouse handlers and must clean up listeners on mouseup/unmount.
- Slider drag dispatches `resize` after commit so tables/editors can recalculate.
- Persisted size key must include layout key and orientation.
- During drag, apply a scoped no-selection class; do not leave global body state behind.
- The layout must be Livewire-morph safe: panes and slider require stable `wire:key`/DOM keys.

Implementation note:

- MVP can implement the Blade markup and vanilla helper first.
- Auto-resize behavior should use a native `ResizeObserver` helper instead of Element Plus `ElAutoResizer`.
- Icons should use the Evo/Tabler icon bridge: vertical slider uses dots-horizontal, horizontal slider uses dots-vertical.

### Table column contract

The table should learn from CareOffice declarative configs and MaryUI table props.

```php
$columns = [
    ['key' => 'id', 'label' => 'ID', 'sortable' => true, 'width' => '80px'],
    ['key' => 'pagetitle', 'label' => 'Title', 'sortable' => true, 'cell' => 'link'],
    ['key' => 'parent', 'label' => 'Parent', 'sortable' => true],
    ['key' => 'published', 'label' => 'Published', 'cell' => 'badge'],
    ['key' => 'actions', 'label' => '', 'cell' => 'actions', 'align' => 'end'],
];
```

MVP cell renderers:

- text
- link
- badge
- boolean
- date
- actions
- slot/custom

Roadmap cell renderers:

- tree path
- TV value
- permission badge
- dirty field
- file preview
- editor status
- draggable handle

## 9. DaisyUI Theme Layer

### Required themes

Support these DaisyUI `data-theme` values:

- `evolight`
- `evolightness`
- `evodark`
- `evodarkness`

Do not treat `liquid` as an active/current theme.

### Manager theme mapping

| Evolution Manager state | Daisy theme |
| --- | --- |
| `light` | `evolight` |
| `lightness` | `evolightness` |
| `dark` | `evodark` |
| `darkness` | `evodarkness` |
| empty/unknown manager state | `evodark` |
| standalone page without manager state | user `prefers-color-scheme` fallback |

Compatibility aliases:

- `evodarknes` -> `evodarkness`
- `dark` -> `evodark`
- `darkness` -> `evodarkness`
- `light` -> `evolight`
- `lightness` -> `evolightness`

### Theme contract

- Apply `data-theme` and `data-theme-mode` to the module root.
- Render the initial theme server-side from Evolution's `EVO_themeMode` cookie or `manager_theme_mode` config before JavaScript runs.
- Mirror to `document.documentElement` and `body` only when the module is isolated in a manager frame and the scope is safe.
- Use CSS variables/tokens from DaisyUI-compatible theme definitions.
- Keep module CSS scoped under `.evo-ui`.
- `.evo-ui` must consume theme variables, not define a hardcoded light theme.
- Modules may add their own tokens only under a package namespace, for example `--devo-table-row-hover`.
- Windows gets custom scrollbars through an OS class.
- macOS keeps native scrollbars; do not force noisy custom scrollbars.
- Theme changes from parent manager must be observed live through:
  - parent `data-theme`;
  - parent body classes;
  - localStorage/cookie keys used by manager, including `EVO_themeMode`;
  - `postMessage` event `evo:theme`.

## 10. Asset Pipeline

### Requirements

- No CDN dependencies for manager UI.
- No per-module Vite/npm build pipeline.
- All CSS/JS lives in source and is built/published through the Evolution asset/Tailwind builder contract.
- Published files must have a transparent map.
- Runtime/demo changes are not source unless symlinked back to package source.
- The package must support development and production asset modes without each module owning its own frontend toolchain.
- Built CSS must scan package Blade/PHP component sources so Daisy/Tailwind classes are not purged.

### Proposed source assets

```text
resources/css/evo-ui.css
resources/js/evo-ui.js
resources/js/components/*.js
```

### Publish map

Publish to:

```text
assets/modules/evo-ui/evo-ui.css
assets/modules/evo-ui/evo-ui.js
```

### Build decision

- evoUI must not introduce `vite.config.js`, local npm scripts, or a module-owned bundler.
- Tailwind/Daisy compilation should be delegated to the Evolution Tailwind builder already available in the host.
- The package exposes source paths and theme tokens; the host builder is responsible for scanning Blade/PHP sources and producing optimized CSS when needed.
- Phase 1 may publish the small authored CSS/JS directly while the builder contract is finalized.

## 11. JavaScript Strategy

### Goal

Keep JavaScript minimal, scoped, and Livewire-compatible. Replace legacy UI dependencies with native helpers.

### Do not use for new code

- jQuery
- Select2
- jQuery UI sortable
- Bootstrap modal
- global `.dropdown`, `.modal`, `.tab-row`, `.sectionBody` behaviors
- CDN-hosted package scripts

### Native helpers

| Helper | Purpose |
| --- | --- |
| `EvoSelect` | Searchable/selectable single and multi-select using native DOM, ARIA, hidden inputs, Livewire sync. |
| `EvoSortable` | Drag/drop reorder with Pointer Events or SortableJS as a vendored optional dependency, scoped to component root. |
| `EvoModal` | Dialog/focus/escape/backdrop helper compatible with Daisy `modal` and Livewire state. |
| `EvoDropdown` | Click/keyboard/outside-click dropdown behavior with local root scope. |
| `EvoTabs` | Overflow-aware tabs, URL/Livewire active state, arrow scrolling. |
| `EvoTree` | Lazy tree expand/collapse/select; future document tree. |
| `EvoEditorAdapter` | Thin lifecycle bridge for TinyMCE/CodeMirror/etc. under `wire:ignore`. |
| `EvoFilePicker` | Manager file browser bridge without leaking globals into components. |

### Livewire compatibility rules

- Every helper initializes from `[data-evo-*]` inside a root scope.
- Every helper must be idempotent: safe to call after Livewire morph.
- Use `Livewire.hook`/Livewire 4 interceptors only through one central adapter.
- Use `wire:ignore` for complex third-party lifecycle widgets.
- Do not store component state only in DOM if Livewire needs it.
- Use `wire:key` for repeatable rows/options/modals/tabs.
- Dispatch explicit custom events under an `evo-ui:*` namespace.
- Avoid global selector collisions.

### JS initialization model

All client helpers initialize from `data-evo-*` attributes. Initialization must be idempotent and scoped.

Required pattern:

```js
if (!el.__evoInitialized) {
    init(el)
    el.__evoInitialized = true
}
```

Rules:

- Do not call `new Component(...)` without checking whether the element is already initialized.
- Re-init passes after Livewire morph must be safe.
- Helper instances should be stored on the element or in a WeakMap.
- Destroy/cleanup must be available for helpers that add global listeners.
- Components should expose explicit data attributes like `data-evo-select`, `data-evo-modal`, `data-evo-layout`, `data-evo-dropdown`.

### Morph safety rules

- All repeatable elements must have stable keys.
- Keys cannot be based on array index.
- Nested components must have their own keys.
- Sortable, list, and tree items always require keys.
- Modal, tab, and layout panes require stable keys.
- Row keys should use persistent ids or deterministic domain keys.

Anti-pattern:

```blade
wire:key="row-{{ $loop->index }}"
```

Correct pattern:

```blade
wire:key="site-content-row-{{ $row->id }}"
```

## 12. Evolution Globals Bridge

The manager already has useful globals and legacy behaviors. evoUI should isolate them behind a small bridge instead of using them directly in every component.

Bridge targets:

- `evo()` config values rendered into `window.EvoUI.config`;
- manager URL/base path;
- current user/permissions where safe;
- `documentDirty` dirty-state compatibility;
- manager file browser / `BrowseServer`;
- editor boot snippets;
- parent frame theme and body classes;
- manager language and translations;
- CSRF token/session data for Livewire.

Rules:

- All global reads go through `window.EvoUI.manager`.
- No component should directly depend on random global functions unless documented.
- If a manager global is missing, the helper must fail gracefully.

## 13. System Contracts

### UI state model

Each component must define one source of truth for each state field. State may live in Livewire, URL, or local Alpine/vanilla helper state, but the same state must not be independently owned in multiple places.

| State | Source of truth | URL sync | Notes |
| --- | --- | --- | --- |
| `search` | Livewire property | yes | Debounced, reloadable, shareable. |
| `filters` | Livewire property | yes for table/resource filters | Use stable scalar/array values, not display labels. |
| `sort.field` | Livewire property | yes | Canonical field key only. |
| `sort.direction` | Livewire property | yes | `asc` or `desc`. |
| `page` | Livewire pagination state | yes | Reset to page 1 when search/filter/sort changes. |
| `perPage` | Livewire property | optional yes | Clamp to allowed values. |
| `selection` | Livewire property | no by default | Array of ids; optional URL sync only for explicit workflows. |
| `activeTab` | Livewire property or URL param | yes for top-level tabs | Back/forward must restore tab. |
| `modalOpen` | Livewire property for server-backed modals | no by default | Local-only modals may use Alpine/helper state. |
| `dropdownOpen` | Local helper state | no | Never persisted unless a component explicitly needs it. |
| layout pane size | localStorage via `x-evo::layout` helper | no | Keyed by layout identity and orientation. |

Rules:

- Livewire owns server-relevant state.
- URL owns shareable/reloadable navigation state.
- Alpine/vanilla helper state owns transient UI only.
- Local state must be recreated safely after Livewire morph.
- Back/forward behavior must restore URL-bound state without stale DOM.

### URL state contract

Canonical table URL params:

- `tab`
- `q` or `search`
- `filter[...]`
- `sort`
- `dir`
- `page`
- `perPage`

Rules:

- URLs must be reload-consistent and shareable.
- Empty/default values should be omitted from the canonical URL.
- Changing `search`, `filters`, `sort`, or `perPage` resets `page`.
- Back/forward navigation must rehydrate the Livewire component from URL state.
- Top-level manager tab state should be reflected in the URL.
- Internal dropdown/modal state should not pollute the URL.

### Event system

All browser events emitted by evoUI must use the `evo-ui:*` namespace.

Standard events:

- `evo-ui:modal.open`
- `evo-ui:modal.close`
- `evo-ui:toast.show`
- `evo-ui:confirm.open`
- `evo-ui:table.reload`
- `evo-ui:table.filter.changed`
- `evo-ui:table.selection.changed`
- `evo-ui:table.sort.changed`
- `evo-ui:table.view.changed`
- `evo-ui:form.saving`
- `evo-ui:form.saved`
- `evo-ui:form.reset`
- `evo-ui:layout.resize`
- `evo-ui:theme.changed`
- `evo-ui:error`

Rules:

- No new un-namespaced global events.
- Livewire lifecycle hooks and helper re-init are bridged only through `EvoLivewireBridge`.
- Browser events are emitted through `window.EvoUI.dispatch()` so event names stay normalized under `evo-ui:*`.
- JS helpers listen inside component scope when possible.
- Global listeners must be registered once and must clean up when applicable.
- Event payloads must be small, serializable, and documented.

### Form and validation flow

Forms are Livewire-first.

Flow:

1. Field components bind with `wire:model`, usually `.defer` or `.blur` for forms.
2. Livewire action validates with rules on submit or field update.
3. Validation errors flow through Laravel/Livewire `$errors`.
4. Field components derive their error state from `error`, `errorField`, or the bound model name.
5. Submit disables the action button and shows a spinner through `wire:loading`.
6. On success, the component dispatches a toast/alert event and clears dirty state.
7. On failure, errors render inline and a standard error alert/toast may appear.

Dirty state:

- Server-backed form changes mark the Livewire form dirty.
- All forms integrate with `DirtyStateBridge`.
- Evolution Manager compatibility must update `documentDirty` through `DirtyStateBridge`.
- Dirty state must clear only after a successful save, explicit reset, or navigation confirmation.
- Unsaved changes in editor/file-picker widgets must sync into the same dirty model.
- Editor and file-picker adapters must emit dirty signals.
- Navigation away from dirty state must open a confirm flow.
- Confirm copy/actions are owned by the consuming module or evoUI i18n defaults.

Optimistic UI:

- MVP should use safe submit by default.
- Optimistic UI is allowed only for reversible low-risk actions such as local row selection.
- Publish/delete/reorder actions must wait for server confirmation.

### Config-driven form layer

The form layer follows the same architecture as the table layer: one generic Livewire renderer, many declarative presets.

```php
'forms' => [
    'site_content' => [
        'source' => ['type' => 'model', 'model' => SiteContent::class, 'key' => 'id'],
        'sections' => [
            ['label' => 'Content', 'show_header' => false, 'fields' => [
                ['name' => 'pagetitle', 'type' => 'text', 'rules' => ['required', 'max:255']],
                ['name' => 'template', 'type' => 'select', 'options_source' => SiteTemplate::class],
            ]],
            ['label' => 'Publication', 'span' => 6, 'fields' => []],
            ['label' => 'Resource options', 'span' => 6, 'fields' => []],
        ],
    ],
]
```

Rules:

- Form layout is declared in config, not hardcoded in Blade.
- `evo-ui.form` is the generic Livewire component; domain modules provide presets.
- Dense resource editors may set `show_header=false` per section; section labels remain semantic config, but the UI does not repeat them when tabs already provide context.
- Sections support 12-column `span`; settings-style sections can sit side by side on desktop and collapse to one column on mobile.
- Field state lives in Livewire `data`.
- Local Alpine is allowed only for field micro-interactions.
- Validation rules are declared per field and translated through field labels.
- Save actions authorize server-side before persisting.
- Field components use `x-evo::` Blade components and Evo/Daisy tokens.
- Dirty-state-ready markup is required; full navigation blocking remains part of `DirtyStateBridge` roadmap.
- First-pass field types: `text`, `textarea`, `number`, `select`, `checkbox`, `date`, `datetime`, `display`, `resource-parent`.
- Forms may define internal tabs and action toolbars in config.
- Form action toolbars stay sticky inside the manager iframe so long resource/TV editors do not require scrolling back to save/copy/delete.
- Fields may define `span`, `hint`, `description`, `invert`, and `save=false`.
- Fields, sections, tabs, and actions may define `permission`, `permissions`, `any_permission`, `role`, or `roles`.
- Field markup may be replaced by config `view` or by `EvoUI::registerFormField($nameOrType, $view)`.
- Table cell markup may be replaced by config `view` or by `EvoUI::registerTableCell($cell, $view)`.
- `invert=true` is used for Evo storage names where the UI label is positive but the DB flag is negative, for example `hidemenu` and `hide_from_tree`.
- `display` fields are read-only UI values and are not persisted by the generic save loop.
- `resource-parent` fields render a hidden Livewire-bound `parent` value plus a manager tree picker bridge; the field saves through `ResourceFormService` and rejects self/descendant parent loops server-side.
- Evo 3.5 tree compatibility is isolated in the JS adapter: the module exposes `window.setParent(id, title)` because the manager tree calls `w.main.setParent(...)` while `tree.ca === "parent"`.
- Roadmap field types: `radio`, `toggle`, `multi-select`, `tags`, `file-picker`, `editor`, `json`, `color`, `repeater`, `relation`.

### Config editor form

Settings forms are config-backed. They should not become large handwritten controllers.

Lessons from existing modules:

- sArticles writes the full settings array into `custom/config/seiger/settings/sArticles.php`.
- sSeo has the safer pattern: load existing settings, merge declared updates, check writability, write stable PHP, clear cache.
- sCommerce shows the breadth of repeated text/select/checkbox/textarea fields that should become declarative config.

evoUI should provide a shared PHP config writer:

- load existing PHP settings file if it exists;
- fallback to package config defaults;
- update only declared keys;
- preserve readable order;
- write a stable PHP array file;
- clear Evolution cache after save;
- show inline success/error state in Livewire.

For MVP, the `settings` tab edits a narrow `devo_ui_settings` preset:

- `module.default_tab`
- `tables.site_content.per_page`
- `tables.site_content.default_view`
- `tables.site_content.enabled_columns`
- `tables.site_content.enabled_filters`
- `forms.site_content.source.default`
- `forms.site_content.enabled_fields`

This proves the contract without turning the settings screen into a generic PHP array editor. Theme mapping remains a manager/theme bridge concern, not a module settings form concern.

### Template layout and resource editor layer

TemplatesEdit proves that Evolution resource editing needs a first-class layout layer, not one hardcoded form. evoUI should keep the idea and replace the implementation.

Reference behavior to preserve:

- Form layout can depend on template id.
- Form layout can depend on manager role.
- A default layout exists when no template-specific layout is configured.
- The layout groups fields into tabs.
- Tabs contain 12-column sections.
- Sections contain default resource fields, TVs, TV categories, and custom fields.
- Unplaced TVs can fall into a default tab.
- Field metadata includes label, help, description, default value, required, readonly, rows, pattern, choices, label position, size, and reverse layout.
- The editor can expose a visual builder for tabs, columns, fields, categories, and imports/exports.

evoUI target contract:

```php
'resource_layouts' => [
    'site_content' => [
        'resolver' => 'template-role',
        'default' => 'default',
        'layouts' => [
            'default' => [
                'tabs' => [
                    ['name' => 'general', 'label' => 'General', 'icon' => 'file-text', 'default' => true],
                    ['name' => 'settings', 'label' => 'Page settings', 'icon' => 'adjustments'],
                ],
                'sections' => [
                    ['tab' => 'general', 'columns' => [
                        ['span' => 12, 'fields' => ['pagetitle', 'longtitle', 'description', 'alias']],
                        ['span' => 12, 'fields' => ['introtext', 'content']],
                    ]],
                    ['tab' => 'settings', 'columns' => [
                        ['span' => 6, 'fields' => ['template', 'parent', 'menuindex']],
                        ['span' => 6, 'fields' => ['published', 'pub_date', 'unpub_date']],
                    ]],
                ],
            ],
        ],
    ],
]
```

Resolution order:

1. template id + role id;
2. template alias + role id;
3. template id default role;
4. template alias default role;
5. global default layout.

Rules:

- The layout resolver returns a normalized array before render.
- The renderer only consumes normalized `tabs`, `sections`, `columns`, and `fields`.
- Legacy keys like `col:0:12`, `fields:0`, and `category:{id}` may be accepted only by an import/migration adapter, not by the public evoUI config.
- Role-specific layout selection is display/config behavior; all saves still re-check permissions server-side.
- The visual builder is roadmap. MVP may edit the normalized config through the settings form, but not build a full drag/drop editor yet.
- Field labels/help come from an Evo resource field catalog, TV metadata, or explicit config override.
- Unknown fields fail closed: render a warning in dev mode and skip in production.
- `#Static` from TemplatesEdit maps to a normal tab/section with `static => true` when needed; it must not become a magic hardcoded branch in Blade.

### Field catalog and TV renderer

TemplatesEdit's `fields.php` and Multifields' element classes show that evoUI needs a central field catalog.

Core catalog entries:

- resource fields: `pagetitle`, `longtitle`, `description`, `alias`, `link_attributes`, `published`, `pub_date`, `unpub_date`, `parent`, `isfolder`, `introtext`, `content`, `richtext`, `weblink`, `template`, `type`, `contentType`, `content_dispo`, `menuindex`, `searchable`, `cacheable`, `createdon`, `editedon`, `menutitle`, `hide_from_tree`, `hidemenu`, `alias_visible`, `syncsite`, `privateweb`, `privatemgr`;
- TV fields loaded from `site_tmplvars` and `site_tmplvar_templates`;
- custom fields registered by modules.

Registry API to add in the roadmap:

```php
EvoUI::registerFieldType('editor', EditorField::class);
EvoUI::registerFieldType('file-picker', FilePickerField::class);
EvoUI::registerTvRenderer('multifields', MultifieldsRenderer::class);
EvoUI::registerFieldTransformer('phpthumb.thumb', ThumbTransformer::class);
EvoUI::registerResourceLayout('site_content.default', $layout);
```

Rules:

- Field type resolution is explicit and whitelisted.
- TV rendering does not call legacy `renderFormElement()` in the new manager UI path.
- A temporary compatibility adapter may read legacy TV definitions, but output still goes through `x-evo::field` components.
- Custom field views are registered through `EvoUI::registerFormField()` or a trusted config `view`.
- Editable module settings must not store arbitrary PHP closures.
- Trusted package source config may reference callables or transformer names, but UI-edited config stores only registered transformer ids.
- File paths, snippet names, editor names, and TV renderer names are validated against registries.

### Repeater and Multifields layer

Multifields is the reference for repeatable nested content, but evoUI must rebuild it as a Livewire/Blade component layer.

Legacy ideas to preserve:

- TV-backed JSON data.
- File-backed JSON data as an optional storage mode.
- Repeatable templates with labels/icons.
- Hidden child templates.
- Nested rows/groups.
- Media/thumb fields.
- Richtext fields inside repeaters.
- Import/export JSON.
- Add, copy, move, delete actions.
- Optional fullscreen editor.
- Breakpoint/grid metadata for content blocks.

evoUI target schema:

```php
'repeaters' => [
    'hero_blocks' => [
        'storage' => ['type' => 'tv', 'name' => 'hero_blocks'],
        'templates' => [
            'slide' => [
                'label' => 'Slide',
                'icon' => 'photo',
                'fields' => [
                    ['name' => 'image', 'type' => 'file-picker', 'accept' => 'image/*'],
                    ['name' => 'title', 'type' => 'text'],
                    ['name' => 'body', 'type' => 'editor'],
                ],
            ],
        ],
    ],
]
```

State contract:

- Repeater state lives in Livewire as a normalized array.
- Every item has a stable generated id.
- Keys never use array indexes.
- Nested repeaters have nested stable ids.
- Reorder sends an ordered list of ids to Livewire.
- Import validates JSON against the registered schema before replacing state.
- Export emits the normalized JSON state.
- Dirty state is emitted through `DirtyStateBridge`.

JS contract:

- Repeater helpers initialize through `data-evo-repeater`, `data-evo-sortable`, and `data-evo-template-picker`.
- Init is idempotent.
- Sortable behavior uses a small scoped helper or a tiny vetted sortable dependency only if native pointer events are not enough.
- No global `Multifields.elements.*`.
- No inline `onclick`.
- No request-dispatched `class`/`method` actions.
- All actions route through Livewire methods with server-side authorization.

Storage contract:

- Resource scalar fields save to `site_content`.
- TV values save through a `TvValueRepository`.
- Repeater JSON can save to TV value or package-managed JSON files depending on config.
- File storage path must be package-owned and not derived directly from client input.
- Migration adapters may import existing Multifields JSON and config, but public evoUI config remains normalized.

### Template/layout builder UI

TemplatesEdit's builder is a good UX reference for layout editing, but its JS and storage model should not be copied.

Roadmap builder primitives:

- `x-evo::builder.palette`
- `x-evo::builder.canvas`
- `x-evo::builder.tab`
- `x-evo::builder.column`
- `x-evo::builder.field`
- `x-evo::builder.settings`

Builder state:

- Source of truth: Livewire normalized layout array.
- Local JS: drag preview and resize interaction only.
- Save: Livewire validates schema and writes config through the PHP config writer.
- Import/export: JSON with schema version.
- Role/template selection: controlled by config resolver and permissions.

Builder permissions:

- View builder: `edit_template` or future package-specific `devo_ui_builder`.
- Save layout: `save_template` or future package-specific `devo_ui_builder_save`.
- Resource form use: `edit_document` / `save_document`.
- TV field visibility still follows TV document-group access and field-level config permissions.

### Migration rules from TemplatesEdit and Multifields

Allowed imports:

- TemplatesEdit PHP/JSON layout files can be converted into normalized `resource_layouts`.
- TemplatesEdit field metadata can seed the field catalog.
- Multifields template configs can be converted into normalized `repeaters`.
- Multifields stored JSON can be read by a compatibility importer and saved back as normalized JSON.

Rejected runtime carry-over:

- `MODX_*` constants in evoUI source.
- Direct `$_SESSION` checks.
- Inline JavaScript handlers.
- FontAwesome-only icon strings.
- `renderFormElement()` as the primary renderer.
- `DLTemplate` for manager UI.
- Request-level dynamic `class`/`method` dispatch.
- Arbitrary PHP closures/snippets stored by the UI.
- Cookie-only builder state.
- Global Sortable instances without idempotent init.

Compatibility rule:

- Compatibility belongs in importers/adapters, not in every component.
- New runtime UI must stay Livewire-first, permission-aware, registry-driven, and componentized under `x-evo::`.

### Permission model

Permissions are enforced server-side first.

Layers:

- Query layer: filter inaccessible rows.
- Livewire action layer: authorize every mutating action.
- Blade layer: hide or disable actions as UX only, never as the only protection.

Baseline permission use cases:

- `view`: can see the module/table/row.
- `edit`: can open and save an edit form.
- `publish`: can toggle published state.
- `delete`: can delete or soft-delete rows.
- `tree access`: can see or interact with a subtree/resource branch.

Rules:

- Config definitions use a shared authorization contract:
  - `permission`: one required Evo manager permission.
  - `permissions`: all listed permissions are required.
  - `any_permission`: at least one listed permission is required.
  - `role` / `roles`: current manager role id must match.
- `Table` filters columns, filters, toolbar actions, and row actions through this contract.
- `Form` filters tabs, sections, fields, toolbar actions, and save action through this contract.
- Components may accept `canEdit`, `canDelete`, etc. for display state.
- Components must not make final authorization decisions on the client.
- Permission-denied actions return a standardized error event and no partial UI mutation.

Current MVP mapping:

- Add/child/copy resource: `new_document`.
- Edit row/form: `edit_document`.
- Save resource form: `save_document`.
- Delete resource: `delete_document`.
- Settings/config save remains package-local and will later map to a dedicated evoUI permission when Evo role management exposes module-specific permissions.

### Security rules

- Every Livewire action must authorize on the server.
- Do not trust client state, hidden inputs, data attributes, selected ids, or disabled buttons.
- Do not execute any mutating action without permission checks.
- Sanitize and validate input where needed, especially filters, search, sort field, action payloads, file paths, editor HTML, and TV input.
- Sort fields must be whitelisted.
- Bulk actions must re-check each target row server-side.
- Client-side hiding is UX only, never security.

### Data layer strategy

MVP may query `EvolutionCMS\Models\SiteContent` directly inside a Livewire component, but reusable modules should move data behavior into services/query objects.

Proposed pattern:

- `ResourceService` for resource/domain actions.
- `ResourceQuery` or table-specific query builders for search/filter/sort.
- `TableColumnDefinition` for reusable column metadata.
- `TableFilterDefinition` for reusable filters.
- `ResourceFormService` for loading, validating, and saving `site_content` plus TVs.
- `ResourceLayoutResolver` for template/role/default form layouts.
- `TvValueRepository` for TV read/write/default/inherited values.
- `LanguageBridge` for sLang-compatible multilingual read/write without coupling Livewire components to sLang internals.
- `RepeaterRepository` for JSON-backed repeatable TV data.
- `FieldCatalog` for resource fields, TV fields, and registered custom fields.

Rules:

- Eager-load relationships and TVs deliberately.
- Avoid N+1 queries in tables.
- TV access must be explicit: `withTVs`, `tvList`, or a evoUI TV adapter.
- Resource form save must use one service boundary; field components must not write directly.
- Multilingual resource fields must save through `LanguageBridge`; default-language data remains in native Evo tables.
- Repeater save must validate against the configured schema before persistence.
- Template/layout config writes must go through the config writer, not ad hoc `file_put_contents`.
- Caching is not required in MVP, but query services should leave room for per-filter option caching and tree caching.
- Reusable query patterns must be deterministic and testable.

### Performance strategy

Rules:

- Search debounce default: `300ms`.
- Use server-side pagination for all manager tables.
- Default `perPage`: `20`; allowed values: `10`, `20`, `50`, `100`.
- Avoid Livewire updates on every keystroke unless debounced.
- Avoid binding large row objects into client-only JS.
- Use lazy loading for heavy tabs and secondary panes.
- Use `wire:key` for row stability.
- Use lightweight table mode for large datasets: fewer columns, no expanded rows, no heavy cell widgets.
- Defer ResourceTree and drag/drop reorder until performance and permission behavior are proven.

### Error handling UX

Standard outcomes:

| Error | UX |
| --- | --- |
| Validation error | Inline field errors plus optional alert summary. |
| Permission denied | Error toast/alert, keep current state, do not mutate row. |
| Livewire request failure | Toast with retry guidance and console diagnostic in dev mode. |
| 500 server error | Error alert/toast, preserve user input where possible. |
| Timeout/network error | Non-blocking toast with retry action. |
| CSRF/session expired | Modal or alert asking for manager reload/login refresh. |

Rules:

- Error messaging is standardized through `evo-ui:error` and `evo-ui:toast.show`.
- Components should expose empty, loading, error, and permission-denied states.
- Do not leave disabled buttons stuck after failed requests.

### Versioning and API stability

evoUI follows SemVer once published.

Breaking changes include:

- removing or renaming a public component;
- removing or renaming public props/slots/events;
- changing default state semantics;
- changing published asset paths;
- changing route names/URLs used by modules;
- changing theme token names without fallback;
- changing permission behavior in a way that lets old modules misrepresent actions.

Rules:

- New props/events are minor releases.
- Bug fixes and visual fixes are patch releases when public API is unchanged.
- Deprecated props/events get at least one minor release of overlap.
- Components should preserve backwards compatibility through adapters where reasonable.

### Testing strategy

Levels:

- PHP unit tests for helpers, query objects, permission guards, config, theme mapping.
- Livewire component tests for state, validation, actions, pagination, events.
- Browser tests for manager render, Livewire update, modal/dropdown/tabs/layout resize, theme switching.
- Asset build tests for generated CSS/JS and manifest.
- Manual QA checklist for Evolution Manager compatibility.

Required MVP browser checks:

- manager page loads without console errors;
- Livewire update route works;
- search debounce works;
- filters/sort/pagination persist through reload;
- back/forward restores table state;
- modal focus and escape behavior works;
- layout resizing persists and restores;
- theme switching updates UI live.

### Module integration contract

Future modules integrate evoUI through:

- Composer dependency.
- Service provider discovery.
- `evo::layouts.manager` layout.
- `x-evo::*` Blade components.
- module-owned Livewire components registered under the module namespace.
- evoUI asset helper/published assets.
- clean routing module registration through Evolution's module API.

Rules:

- evoUI owns shared layout/assets/components.
- Feature modules own their routes, Livewire components, permissions, and data services.
- Modules may extend table columns/actions through documented arrays/slots.
- Modules must not publish over evoUI core assets.
- New modules should prefer routing modules over legacy execute-module processors.

### Extension system

evoUI must provide a registry for controlled extension instead of ad hoc global overrides.

Required registration API:

```php
EvoUI::registerComponent('resource-status', ResourceStatus::class);
EvoUI::registerTableCell('status-badge', StatusBadgeCell::class);
EvoUI::registerFilter('template', TemplateFilter::class);
EvoUI::registerAction('publish-toggle', PublishToggleAction::class);
```

Extension points:

- new Blade/Livewire components;
- table cell renderers;
- table filters;
- table actions;
- toolbar actions;
- icon resolvers;
- theme token providers.

Rules:

- Registrations happen in service providers.
- Names are namespaced or collision-checked.
- Core registrations cannot be silently overwritten.
- Extension APIs are part of the SemVer contract.
- Extensions must still follow permission, state, event, and morph-safety rules.

### Accessibility rules

- Buttons and icon-only controls need labels, `aria-label`, or tooltips.
- Modal uses focus trap, escape close unless persistent, and returns focus to trigger.
- Dropdown supports escape, outside click, keyboard navigation where practical.
- Tabs use proper roles/aria state and keyboard navigation.
- Table actions are keyboard reachable.
- Resizable layout slider exposes `role="separator"`, `aria-orientation`, and keyboard resize later; MVP must at least label it.
- Loading states should not remove focus unexpectedly.

### Responsive strategy

- Manager UI is desktop-first but must not break on tablet/mobile.
- Tables use horizontal scroll by default in MVP.
- Future responsive table mode can collapse lower-priority columns.
- Toolbars wrap predictably and keep primary actions reachable.
- Layout panes can stack on small screens when configured.
- Drag/resize helpers should support pointer events for touch where possible.
- Touch drag/drop reorder is roadmap, not MVP.

### Icon system

- Default icon set: Tabler through Evolution's native Blade Icons path.
- `x-evo::icon` is only a thin wrapper over `svg('tabler-*')`; it must not keep a local SVG registry.
- Component prop format should be simple: `icon="plus"`, `icon-right="chevron-right"`, normalized internally to `tabler-plus`.
- Custom icons are allowed through slots or a documented icon resolver extension.
- Icon-only buttons must include `aria-label` or `tooltip`.
- Components must not embed raw SVG while the icon exists in the Blade Icons/Tabler set.

### i18n support

- Components accept rendered labels, not translation keys only.
- Module authors may pass `__('namespace::file.key')` results directly.
- evoUI system text lives in package translation files.
- Manager language is read from Evolution manager config/session.
- Components should avoid hardcoded English in UI output.
- Multi-language manager modules can use the same layout/components without changing evoUI internals.

### Resource multilingual contract

evoUI does not import sLang runtime classes into Livewire components. The compatibility point is `LanguageBridge`.

Rules:

- Source of truth for default locale stays native Evo: `site_content` and `site_tmplvar_contentvalues`.
- Non-default resource translations use an adapter contract compatible with `s_lang_content`.
- Non-default multilingual TV values use an adapter contract compatible with `s_lang_tmplvar_contentvalues`.
- Enabled locales are resolved from form config first, then `s_lang_config`, then manager/default language.
- Translatable resource fields are declared in form config, defaulting to the sLang content field set: `pagetitle`, `longtitle`, `description`, `introtext`, `content`, `menutitle`, `seotitle`, `seodescription`.
- Multilingual TVs are declared in form config or read from `s_lang_tvs`.
- Livewire receives locale as state/URL state, but `Form` never writes translation tables directly.
- If sLang tables are absent, the bridge stays inert and the resource form works as a normal Evo form.

Current phase:

- `ResourceFormService` is the single load/save boundary for resource fields and TVs.
- `LanguageBridge` is present as an sLang-compatible contract.
- UI language switcher and translated tabs are the next layer, after the service boundary is stable.

## 14. MVP

### MVP deliverable

A base Evolution CMS module `evoUI` with:

- package skeleton;
- manager module registration;
- working Livewire bridge;
- DaisyUI build with Evo themes;
- base manager layout;
- demo page;
- core components:
  - Button
  - Input
  - Textarea
  - Select
  - Checkbox
  - Badge
  - Alert
  - Card
  - Table
  - Pagination
  - Modal
  - Dropdown
  - Tabs
  - Toolbar
  - Layout
  - Form
- one demo Livewire table backed by `EvolutionCMS\Models\SiteContent`;
- one demo Livewire form backed by `EvolutionCMS\Models\SiteContent`;
- one config-backed settings form backed by `custom/config/evo-ui.php`;
- first tab: `site_content`;
- search, filters, pagination, row actions, loading state, empty state, and theme-aware UI.

### SiteContent demo table

The first tab should show `site_content` records.

Baseline columns:

- ID
- pagetitle
- alias
- parent
- template
- published
- searchable
- menuindex
- createdon / editedon
- actions

### SiteContent demo form

The `form` tab should render `<livewire:evo-ui.form preset="site_content" />`.

Baseline fields:

- pagetitle
- longtitle
- description
- alias
- link_attributes
- introtext
- template
- menutitle
- menuindex
- show in menu mapped to inverted `hidemenu`
- parent display
- content
- published
- pub_date
- unpub_date
- type
- contentType
- content_dispo
- isfolder
- alias_visible
- richtext
- show children mapped to inverted `hide_from_tree`
- searchable
- cacheable
- clear cache runtime option
- privateweb
- privatemgr

The form must be config-driven, validate server-side, render inline field errors, expose `General` and `Page settings` inner tabs, and save safely through the generic form component.

### evoUI settings form

The `settings` tab should render `<livewire:evo-ui.form preset="devo_ui_settings" />`.

Baseline fields:

- default tab
- site_content per page
- site_content default view
- site_content enabled columns
- site_content enabled filters
- site_content default resource
- site_content enabled form fields

The save action updates only declared config keys and writes to `custom/config/evo-ui.php`.

Baseline filters:

- text search by `pagetitle`, `longtitle`, `alias`, `description`, `introtext`
- parent selector
- template selector
- published/unpublished/all
- searchable/unsearchable/all

Baseline row actions:

- open in manager edit screen
- open frontend URL
- copy ID
- optional publish toggle after permissions are checked

SiteContent patterns to support later:

- `withTVs([...])`
- `tvFilter(...)`
- `tvOrderBy(...)`
- `GetRootTree(...)`
- `descendantsOf(...)`
- `ancestors()`
- tree conversion with `toTree()`

## 15. Developer Experience

### Future module usage

Package install:

```php
// composer.json of a future module
"require": {
    "evolution-cms/evolution": ">=3.5.6 <4.0",
    "middleduck/evo-ui": "^0.1"
}
```

Manager layout:

```blade
@extends('evo::layouts.manager')

@section('content')
    <livewire:evo-ui.table preset="site_content" />
@endsection
```

Component use:

```blade
<x-evo::toolbar>
    <x-slot:left>
        <x-evo::button icon="plus" label="Create" class="btn-primary" />
    </x-slot:left>

    <x-slot:right>
        <x-evo::input wire:model.live.debounce.300ms="search" icon="search" placeholder="Search" />
    </x-slot:right>
</x-evo::toolbar>

<x-evo::table
    :columns="$columns"
    :rows="$rows"
    row-key="id"
    :loading="$loading ?? false"
/>
```

Table preset:

```php
'tables' => [
    'site_content' => [
        'model' => EvolutionCMS\Models\SiteContent::class,
        'columns' => [
            ['key' => 'id', 'field' => 'id', 'label' => 'ID'],
            ['key' => 'pagetitle', 'field' => 'pagetitle', 'label' => 'evo::global.column_pagetitle'],
        ],
        'filters' => [
            ['type' => 'segmented', 'state' => 'published', 'default' => 'all'],
        ],
    ],
]
```

### DX rules

- One module can opt into `evo::layouts.manager` and get assets/theme/Livewire scripts automatically.
- Tables accept declarative `columns`, `filters`, `actions`, and `rowKey`.
- Search/filter/sort/pagination state should be either Livewire URL state or explicit component properties.
- Validation errors render through field components.
- Modal confirm uses `x-evo::modal` with `wire:model`.
- Toast notifications use a future `EvoToast` bridge; MVP can include alert-only behavior or a tiny toast helper if needed.

## 16. Quality Requirements

- Clean package code, no random inline JS.
- No CDN dependencies in manager UI.
- No runtime/demo-only source changes.
- All assets live in package source and publish to a transparent map.
- All components documented.
- Every component has a demo/example.
- CSS classes are scoped.
- Do not break old Evolution Manager.
- Do not override manager globals.
- Do not assume MaryUI is installed.
- No hard dependency on Bootstrap/jQuery.
- Livewire routes are manager-scoped and permission-protected.
- Demo is git-ignored and disposable.
- Each MVP component has basic render coverage or a manual checklist.

## 17. Risks

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Livewire dependency conflicts | Composer install fails on Evolution 3.5.6+ | Keep dependency set narrow and test in the demo runtime. |
| Missing Laravel foundation classes | Runtime errors from Livewire | Keep only the small shims Livewire 4 touches in Evo 3.5.6+. |
| CSRF/session mismatch | Livewire update fails or becomes insecure | Keep `mgr` middleware, verify `web` requirements, test authenticated manager requests. |
| Manager iframe asset loading | Scripts/styles load from wrong base URL | Central asset URL helper and manifest-aware publish map. |
| Morphing conflicts with custom JS | Select/dropdown/sortable loses state | Idempotent helpers, `wire:key`, `wire:ignore`, central Livewire hook adapter. |
| Theme mismatch | UI wrong in dark/light manager modes | ManagerThemeBridge reads parent frame, storage, body classes, messages. |
| Alpine/Livewire conflicts | Double state or stale UI | Alpine only for small local state; keep Livewire as source for server state. |
| Performance on large tables | Slow `site_content` queries/rendering | Paginate server-side; later add ResourceTable virtual/compact mode if needed. |
| Document tree complexity | Tree becomes a second manager | Defer full tree to roadmap; MVP table only. |
| Drag/drop reorder | Data corruption risk | Defer reorder actions until permissions and transaction model are defined. |
| Editor lifecycle | Content loss or morph bugs | EditorAdapter must use `wire:ignore` and explicit sync events. |
| File browser integration | Manager globals vary by version | FilePickerBridge wraps `BrowseServer`/manager file APIs behind feature detection. |
| Publish contract drift | Demo works but package install fails | Always modify source assets and publish map; demo remains disposable. |

## 18. Alternatives Considered

### Use MaryUI directly

Rejected for product foundation. MaryUI is excellent as a reference, but evoUI needs Evo-native namespace, manager theme bridge, manager globals bridge, Evolution package publish contract, and no hard Mary dependency.

### Build only plain Blade without Livewire

Rejected for MVP because future modules need Livewire-first stateful tables/forms/modals. Plain Blade can remain supported, but Livewire is the primary DX.

### Keep sArticles AJAX model

Partially accepted. sArticles AJAX URL state and table filtering are useful, but evoUI should use Livewire for new reactive components instead of manual DOM replacement as the default.

### Use jQuery plugins for MVP speed

Rejected. The foundation must not begin by importing legacy dependencies that it is meant to replace.

### Port TemplatesEdit and Multifields as-is

Rejected for the new runtime. Both packages contain valuable product patterns, but the implementation depends on legacy manager globals, inline handlers, direct sessions, old constants, dynamic request dispatch, and non-Livewire state. evoUI should import their ideas into normalized configs, registries, and adapters, not reuse their runtime architecture.

## 19. Roadmap Components

Not required for MVP, but the architecture must leave space for:

- `ResourceTable`
- `ResourceEditor`
- `ResourceTree`
- `TvRenderer`
- `FilePickerBridge`
- `EditorAdapter`
- `DirtyStateBridge`
- `PermissionGuard`
- `ManagerThemeBridge`
- `Toast`
- `Confirm`
- `BulkActions`
- `ColumnSettings`
- `SavedViews`
- `FieldCatalog`
- `ResourceLayoutResolver`
- `TemplateLayoutBuilder`
- `MultifieldsBridge`
- `Repeater`
- `RepeaterTemplatePicker`
- `TvValueRepository`
- `ResourceFormService`
- `FieldTransformerRegistry`

## 20. Implementation Plan

### 1. Audit repositories and dependencies

- Keep the demo runtime on Evolution CMS 3.5.6+.
- Re-check Livewire 4 install on that host.
- Document only the active Evo 3.5.6+ bridge contract in `UiBook/LIVEWIRE-BRIDGE.md`.

### 2. Minimal package skeleton

- Create `composer.json` as `evolutioncms-module`.
- Add provider, routing module registration, routes, config, views, assets folders.
- Add `.gitignore` with `demo/` and local tool temp outputs.

### 3. Livewire bridge

- Implement manager-scoped Livewire routes.
- Register component namespaces and layout.
- Add foundation shims only when missing.
- Verify CSRF/session/mgr middleware behavior.

### 4. DaisyUI/Tailwind builder

- Add Tailwind/Daisy source files.
- Include source scanning for package views/components.
- Integrate with the Evolution Tailwind builder instead of adding Vite.
- Publish assets to `assets/modules/evo-ui`.

### 5. Theme bridge

- Port the sArticles theme lessons into `ManagerThemeBridge`.
- Support `evolight`, `evolightness`, `evodark`, `evodarkness`.
- Add OS scrollbar classes and parent frame sync.

### 6. Base layout

- Create `evo::layouts.manager`.
- Include theme root, assets, Livewire styles/scripts, CSRF meta.
- Keep manager-safe markup and scoped root class.

### 7. Core components

- Implement MVP components one by one with docs and demos.
- Start with Button, Input, Select, Table, Pagination, Modal, Tabs, Toolbar.
- Add the remaining MVP components after table demo proves the structure.

### 8. Demo Livewire table

- Add `<livewire:evo-ui.table preset="site_content" />` as the first tab.
- Use `EvolutionCMS\Models\SiteContent`.
- Implement columns, search, filters, pagination, loading, empty state, and actions through table config.
- Add permission-aware row actions.

### 8.1 Demo Livewire form

- Add `form` tab to the module shell.
- Add `<livewire:evo-ui.form preset="site_content" />`.
- Define `forms.site_content` in config.
- Render fields through EvoUI field components.
- Save to `EvolutionCMS\Models\SiteContent` with server-side validation.
- Keep form markup declarative and component-based.

### 8.2 Config editor form

- Define `forms.devo_ui_settings`.
- Render it in the `settings` tab.
- Add a shared PHP config writer.
- Save only declared keys into `custom/config/evo-ui.php`.
- Clear Evo cache after save.

### 8.3 Resource form foundation hardening

- Add `FieldCatalog` for core resource fields and labels/help.
- Add `ResourceLayoutResolver` with default/template/role resolution.
- Add `ResourceFormService` as the single save/load boundary.
- Add `TvValueRepository` for template variable values.
- Add `LanguageBridge` for sLang-compatible multilingual fields and TVs.
- Keep the first resource form declarative and leave the visual builder for roadmap.

### 9. Documentation

- Add `UiBook/COMPONENTS.md`.
- Add `UiBook/THEMES.md`.
- Add `UiBook/LIVEWIRE-BRIDGE.md`.
- Add examples for each component.
- Add `UiBook/FORMS.md` for config-driven forms, field catalog, layout resolver, resource/config services, TV storage, and multilingual bridge.
- Add `UiBook/REPEATERS.md` before implementing the Multifields replacement.

### 10. Tests/checklist

- Composer validate.
- PHP syntax check.
- Asset build.
- Demo install smoke check.
- Manager page render.
- Livewire update request.
- Theme switching light/dark/lightness/darkness.
- Windows/macOS scrollbar behavior.
- Table search/filter/pagination/action checks.

### 11. Resource UI roadmap

- Design `ResourceTable`, `ResourceEditor`, `ResourceTree`.
- Define TV rendering.
- Define editor and file picker bridges.
- Define dirty state and permission guard.
- Design `Repeater` and `MultifieldsBridge` from the audited Multifields contracts.
- Design `TemplateLayoutBuilder` from the audited TemplatesEdit builder contracts.
- Add migration importers for TemplatesEdit layouts and Multifields repeater configs after the new normalized schema is stable.

## 21. Open Questions

1. Which Composer vendor should publish package `evo-ui`?
2. Should demo use SQLite by default like the sArticles demo contract?
3. Which manager permission should guard the evoUI demo module?
4. Should `EvoSelect` be built fully native in MVP, or should MVP start with native `<select>` and roadmap the searchable version?
7. Should full `ResourceTree` wait until after the table foundation, or should a minimal parent tree filter be included in the first `site_content` tab?
8. Should the first visual layout builder edit only evoUI normalized JSON, or should it also import legacy TemplatesEdit JSON in the same screen?
9. Should Multifields file-backed JSON storage remain supported for migrated TVs, or should new repeaters default to TV-value JSON only?
10. Which permission names should guard package-specific builder/config screens before Evo exposes module-local permission definitions?

## 22. First Implementation Boundary

After this PRD is accepted, the first code pass should create only the minimal package skeleton, bridge, build assets, layout, and first table demo. It should not attempt to port sArticles, replace Evolution manager, or build the full roadmap in one pass.
