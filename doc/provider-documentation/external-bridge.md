# External API Bridge

_Auto-generated from `services/external-bridge/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `external-bridge` |
| Folder | `services/external-bridge/` |
| Chat command | `@api` |
| Type | `bridge` |
| Enabled | yes |
| Requires login | yes |
| Billing | free |
| Visual template | no (uses default card) |

Ad-hoc read/write relay to any HTTPS API — url/method/headers/body supplied per call, nothing stored server-side.

## Calling it

```
POST /?action=service_run
Content-Type: application/json

{ "service": "external-bridge", "input": { "action": "request" } }
```

## Full details

See `services/external-bridge/README.md` for provider-specific notes.

_See also: `services/bridge/` for a **named-route** version of this same
idea (Canva, Drive, maps, ...) if you want reusable configured endpoints
instead of a raw URL on every call._
