# Auto deploy (docs.stream-platform.cloud) without SSH

Static guides site — deploy is `git pull` via webhook.

## One-time setup

```bash
cd /var/www/html/docs.stream-platform.cloud
./deploy.sh
php bin/setup_deploy_webhook_secret.php
```

GitHub → Webhooks:

| Field | Value |
|-------|--------|
| Payload URL | `https://docs.stream-platform.cloud/deploy_webhook.php` |
| Content type | `application/json` |
| Secret | from the setup script |
| Events | Just the **push** event |

`ping` → `pong`. Push to `main` → `202`.

Logs: `data/deploy-logs/`

If DocumentRoot is the repo root (not `guides/`), also expose the webhook or point Apache at `guides/` (recommended).
