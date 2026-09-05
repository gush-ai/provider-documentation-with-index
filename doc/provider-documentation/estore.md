# SStore Shopping

_Auto-generated from `services/estore/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `shop` |
| Folder | `services/estore/` |
| Chat command | `@shop` |
| Type | `ecommerce` |
| Enabled | yes |
| Requires login | no |
| Billing | free |
| Visual template | yes — scope `gsc-tpl-estore` |

Search SStore products, browse categories, view product details, reviews, cart actions and place orders.

> **Note:** this manifest's `key` is `shop`, same as `services/shop/manifest.php`'s
> key — that's a pre-existing collision in the shipped services (see the
> comment in `services/ecommerce/manifest.php` about the same class of bug
> with `@shop`/`@store`). `matchServiceCommand()` in `main12.js` takes the
> first match it finds scanning services alphabetically, so only one of
> `estore`/`shop` is reachable by key today. Worth giving one of them a
> distinct `key` the same way `ecommerce` was renamed to `store`.

## Actions

- `search`
- `list_products`
- `product_details`
- `get_categories`
- `get_reviews`
- `add_review`
- `add_reply`
- `toggle_like`
- `add_to_cart`
- `save_for_later`
- `stock_alert`
- `create_order`
- `instant_buy`

## Calling it

```
POST /?action=service_run
Content-Type: application/json

{ "service": "shop", "input": { "action": "search" } }
```

## Configuration

This provider ships `config.php.example` — copy it to `config.php` inside
`services/estore/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.

## Visual template

This provider renders with its own card/modal design instead of the
default shopping card.

- CSS: `services/estore/template/style.css`
- JS: `services/estore/template/render.js`
- Scope class: `gsc-tpl-estore`

See `HOW-TO-BUILD-A-PROVIDER.md` for the template contract.

## Full details

See `services/estore/README.md` for provider-specific notes.
