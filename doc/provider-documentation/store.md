# Ecommerce Bridge

_Auto-generated from `services/ecommerce/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `ecommerce` |
| Folder | `services/ecommerce/` |
| Chat command | `@store` |
| Type | `ecommerce` |
| Enabled | yes |
| Requires login | yes |
| Billing | free |
| Visual template | no (uses default card) |

Read and write products/orders on any REST-based store (WooCommerce-compatible presets included, plus a generic "request" action for anything else).

## Actions

- `list_products`
- `get_product`
- `create_product`
- `update_product`
- `list_orders`
- `get_order`
- `create_order`
- `update_order`
- `request`

## Calling it

```
POST /?action=service_run
Content-Type: application/json

{ "service": "ecommerce", "input": { "action": "list_products" } }
```

## Configuration

This provider ships `config.php.example` — copy it to `config.php` inside
`services/ecommerce/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.

## Full details

See `services/ecommerce/README.md` for provider-specific notes.
