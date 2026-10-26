# Kyvora

Local WordPress hardening. This is the WordPress.org directory version: every feature works after activation. There is no account, license check, or remote service.

Product site: https://wpprotectyoursite.com

## Layout

The plugin bootstrap is `kyvora.php`. WordPress reads `readme.txt`.

## Release zip

The zip must contain a top-level folder named `kyvora`.

```bash
cd ..
zip -r kyvora.zip kyvora -x '*.git*'
```
