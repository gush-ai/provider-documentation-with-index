# How to build a new Gush AI provider (with its own look)

This is the one page to read before adding a provider — whether that's a
person doing it or an AI assistant asked to "add a Canva provider like the
shop one." Everything else in `doc/provider-documentation/` is
auto-generated *from* what you build here.

## 1. A provider is a folder, always two required files

```
services/<key>/
  manifest.php     required — metadata (name, command, pricing, ...)
  generate.php      required — the actual provider call
  config.php          your real secrets (never committed)
  config.php.example   placeholder shape of config.php (committed)
  README.md             optional, provider-specific notes
  template/               optional — this provider's own visual design
    style.css
    render.js
```

Drop a folder like this in and it's discoverable immediately via
`?action=get_available_services` and callable via `?action=service_run` —
nothing else in the app needs to change. Full backend contract (manifest
fields, the `{'ok' => bool}` closure shape, billing, security) is in
`/services/README.md` — read that first if `generate.php` is new to you.
This page only covers what's new: **giving a provider its own card design**
and **the documentation step**.

## 2. Default look vs. your own look

Any provider whose success response looks like:

```php
['ok' => true, 'products' => [ ['id'=>..,'name'=>..,'price'=>..,'image_url'=>..], ... ]]
```

gets a 2-column card grid and a single-product popup **automatically**,
styled with the app's own default look — the same one `estore` used to
have hardcoded. You don't have to do anything for this. If that default
look is good enough for your provider, skip straight to step 4.

If you want your own design — different card layout, extra fields, brand
colors, a custom "buy" flow — add a `template` block to `manifest.php`:

```php
'template' => [
    'enabled' => true,
    'css'     => 'style.css',                 // services/<key>/template/style.css
    'js'      => 'render.js',                  // services/<key>/template/render.js (optional)
    'scope'   => 'gsc-tpl-<key>',               // MUST be unique across every provider
],
```

## 3. The template contract

**`template/style.css`** — every selector nested under `.{scope}`. The
frontend wraps everything your provider renders (card grid and modal) in
an element carrying that class, so your CSS can never leak onto another
provider's cards, and another provider's CSS can never leak onto yours.
Don't touch `:root`, `body`, or unscoped element selectors.

**`template/render.js`** (optional) — registers itself on a shared
registry, keyed by your provider's `key`:

```js
window.GushProviderTemplates = window.GushProviderTemplates || {};
window.GushProviderTemplates['<key>'] = {
  scope: 'gsc-tpl-<key>',
  renderCard(product, ctx)  { return '<div class="...">...</div>'; },   // optional
  renderModal(product, ctx, closeModal) { return '<div>...</div>'; },     // optional
  onAction(action, product, ctx) { return false; },                        // optional
};
```

Both `renderCard` and `renderModal` are optional independently — ship only
the one you need a different look for; the other falls back to the app's
default. `ctx` gives you `{ esc, formatPrice, currencySymbol, provider,
providerName, scope }` — always run product text through `ctx.esc()`,
never interpolate raw fields into your HTML string.

Both assets are served through a sandboxed passthrough
(`?action=service_asset&service=<key>&file=<name>`, see
`/service_asset.php`) that can only ever reach files inside
`services/<key>/template/` — it physically cannot serve `manifest.php`,
`generate.php`, or `config.php`, however the file is named.

**If your renderer throws**, the app logs a warning and falls back to the
default card/modal instead of breaking the chat bubble — a broken custom
template degrades gracefully, it never takes down the conversation.

## 4. Never put a real secret anywhere in this pass

- `config.php.example` (committed) — placeholder values only, prefixed
  `YOUR_`/`REPLACE_`/`CHANGE_`. Real providers refuse to run against a
  value still carrying that prefix — a forgotten setup step fails loudly.
- `config.php` (never committed) — real credentials, read only with
  `require`, never included in any JSON response back to the browser.
- `manifest.php`, `README.md`, `template/*` — never contain a real
  endpoint, key, or token. They're what this doc generator reads, and
  what an AI assistant reads to learn the pattern — treat them as public.

## 5. Generate its documentation page

```
php tools/gen-provider-docs.php
```

This scans every `services/<key>/manifest.php` (never `config.php`) and
(re)writes `doc/provider-documentation/<key>.md` plus the index
`README.md`. Re-run it any time you add a provider or change a manifest —
it's a doc scaffold, safe to run repeatedly, and it's how an AI assistant
working in this codebase later can `read doc/provider-documentation/` and
already know what every installed provider does and how to add another
one, without ever seeing a real credential.

## 6. Checklist

- [ ] `manifest.php` — key, name, command (unique — check for collisions
      the way `ecommerce`/`estore` had to be renamed, see `/services/README.md`)
- [ ] `generate.php` — returns `['ok'=>bool, ...]`, shape it as
      `{products:[...]}` to get the card grid for free
- [ ] `config.php.example` copied to `config.php`, filled in
- [ ] (optional) `template/style.css` scoped under a unique `.{scope}`
- [ ] (optional) `template/render.js` registered under your provider `key`
- [ ] `php tools/gen-provider-docs.php`
