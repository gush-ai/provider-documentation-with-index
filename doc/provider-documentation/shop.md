# Shopping

_Auto-generated from `services/shop/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `shop` |
| Folder | `services/shop/` |
| Chat command | `@shop` |
| Type | `ecommerce` |
| Enabled | yes |
| Requires login | no |
| Billing | free |
| Visual template | no (uses default card) |

Search SStore products, browse categories, view product details, reviews, cart actions and place orders.

> **Same collision as `estore.md`** — `services/shop/manifest.php` and
> `services/estore/manifest.php` both declare `key => 'shop'` and
> `command => '@shop'`. Only one is actually reachable at runtime; the
> other is dead weight until one of the two keys is renamed. Worth
> resolving before adding more providers so the pattern isn't copied again.

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
`services/shop/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.

## Full details

See `services/shop/README.md` for provider-specific notes.
