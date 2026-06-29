# Freizeitexperten Plugin-Arbeitsordner

Dieser Ordner ist die lokale Werkbank fuer die beiden WordPress-Konfigurator-Plugins.

Startstand: 2026-06-28

- Quelle: `LIVE Website Freizeitexperten\FTP\_dev.freizeitexperten.de\wp-content\plugins`
- `niers-kombi-konfigurator`: Version `1.7.67`
- `niers-konfigurator`: Version `9.16`

Live-Deployment erfolgt manuell durch Chris per FTP auf `freizeitexperten.de`.
Codex bereitet lokal gepruefte `.php`-Dateien vor und fasst Live nicht direkt an.

## Aktueller DEV-Stand

- `niers-konfigurator` Version `9.18`
  - optionale API-v1-Warenkorb-Vorpruefung per `niers_konfigurator_api_v1_base_url`
  - ruft bei gesetzter URL `POST /api/v1/cart_validate.php` vor der bisherigen Warenkorb-Uebergabe auf
  - alter Warenkorb-/Checkout-Flow bleibt unveraendert, wenn die Option leer ist

Fuer die DEV-Website kann im WordPress-Admin unter `Einstellungen > Niers Regeln` als API-v1 Basis-URL eingetragen werden:

```text
https://erp.ki-experte-derix.de/api/v1
```
