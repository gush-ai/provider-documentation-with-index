# Bridge Hub

_Auto-generated from `services/bridge/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `bridge` |
| Folder | `services/bridge/` |
| Chat command | `@bridge` |
| Type | `bridge` |
| Enabled | no |
| Requires login | yes |
| Billing | free |
| Visual template | no (uses default card) |

Named gateway to multiple external APIs (Canva, Drive, maps, security scanners, ...) configured once in config.php and called by route name.

## Calling it

```
POST /?action=service_run
Content-Type: application/json

{ "service": "bridge", "input": { "action": "list_routes" } }
```

## Configuration

This provider ships `config.php.example` — copy it to `config.php` inside
`services/bridge/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.

## Full details

See `services/bridge/README.md` for provider-specific notes. Currently
`enabled => false` in the manifest — flip it to `true` once at least one
route in `config.php` is filled in.
