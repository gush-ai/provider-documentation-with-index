# Wallet Top-Up (Paystack)

_Auto-generated from `services/payment/manifest.php` by `tools/gen-provider-docs.php`._
_Do not hand-edit — re-run the generator after changing the manifest instead._

| Field | Value |
|---|---|
| Key | `payment` |
| Folder | `services/payment/` |
| Chat command | `@topup` |
| Type | `payment` |
| Enabled | yes |
| Requires login | yes |
| Billing | free |
| Visual template | no (uses default card) |

Fund your Gush AI wallet balance in-app via Paystack — cards, bank transfer, USSD.

## Calling it

Payment-type services use two dedicated actions instead of the generic
`?action=service_run` — see `services/payment/README.md`:

```
POST /?action=payment_init
POST /?action=payment_verify
```

## Configuration

This provider ships `config.php.example` — copy it to `config.php` inside
`services/payment/` and fill in real credentials. **This document never
shows config.php contents** — it is generated from `manifest.php` only, which
never holds secrets by convention.

## Full details

See `services/payment/README.md` for provider-specific notes.
