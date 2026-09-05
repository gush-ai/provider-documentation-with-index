# YarnGPT Voice

_Auto-generated from `services/yarngpt/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `yarngpt` |
| Folder | `services/yarngpt/` |
| Chat command | `@voice` |
| Type | `voice` |
| Enabled | yes |
| Requires login | yes |
| Billing | ₦20 after 3 free use(s) |
| Visual template | no (uses default card) |

Generate Nigerian voice audio with YarnGPT.

## Calling it

Voice uses its own dedicated action instead of the generic
`?action=service_run` (see `/services/README.md` §3.2):

```
POST /?action=generate_speech
```

## Configuration

This provider ships `config.php.example` — copy it to `config.php` inside
`services/yarngpt/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.
